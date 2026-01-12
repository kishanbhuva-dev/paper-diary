<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Stripe\Stripe;

class StripeController extends Controller
{
    public function getProducts(Request $request)
    {
        Stripe::setApiKey(config('cashier.secret'));

        $productId = config('services.stripe.product');

        if (empty($productId)) {
            return response()->json(['status' => false, 'message' => 'Stripe product ID is not configured in .env file.'], 500);
        }

        try {
            $product = \Stripe\Product::retrieve($productId);
            $prices = \Stripe\Price::all(['product' => $product->id, 'active' => true]);

            $productsData = [
                'id'          => $product->id,
                'name'        => $product->name,
                'description' => $product->description,
                'prices'      => $prices->data,
            ];

            return response()->json(['status' => true, 'data' => [$productsData]]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getStripeConfig()
    {
        return response()->json([
            'status' => true,
            'data'   => [
                'publishableKey' => env('STRIPE_KEY'),
            ],
        ]);
    }

    public function createSubscription(Request $request)
    {
        try {
            $user = auth()->user();

            if (! $user || $user->role !== 'owner') {
                return response()->json([
                    'status'  => false,
                    'message' => 'Only owners can create subscriptions',
                ], 403);
            }

            $request->validate([
                'priceId'         => 'required|string',
                'paymentMethodId' => 'required|string',
            ]);

            Stripe::setApiKey(config('cashier.secret'));

            if ($user->hasActiveSubscription()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'You already have an active subscription',
                ], 400);
            }

            if (! $user->stripe_id) {
                $stripeCustomer = \Stripe\Customer::create([
                    'email'    => $user->email,
                    'name'     => $user->firstName . ' ' . $user->lastName,
                    'metadata' => ['user_id' => $user->id],
                ]);
                $user->stripe_id = $stripeCustomer->id;
                $user->save();
            }

            $existingSubscriptions = \Stripe\Subscription::all([
                'customer' => $user->stripe_id,
                'status'   => 'active',
                'expand'   => ['data.latest_invoice.payment_intent'],
            ]);

            if (count($existingSubscriptions->data) > 0) {
                $existingSub = $existingSubscriptions->data[0];

                try {
                    $userSubscription = Subscription::where('stripe_id', $existingSub->id)->first();
                    if (! $userSubscription) {
                        $userSubscription = Subscription::create([
                            'user_id'       => $user->id,
                            'type'          => 'default',
                            'stripe_id'     => $existingSub->id,
                            'stripe_status' => 'active',
                            'stripe_price'  => $existingSub->items->data[0]->price->id ?? 'default',
                            'quantity'      => 1,
                        ]);
                    } else {
                        $userSubscription->stripe_status = 'active';
                        $userSubscription->ends_at = null;
                        $userSubscription->save();
                    }

                    return response()->json([
                        'status'  => true,
                        'message' => 'Subscription activated successfully',
                        'data'    => [
                            'subscriptionId' => $existingSub->id,
                            'status'         => 'active',
                            'paymentStatus'  => 'activated',
                            'redirect'       => '/owner',
                        ],
                    ]);
                } catch (Exception $activateException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Unable to activate subscription: ' . $activateException->getMessage(),
                        'data'    => null,
                    ], 500);
                }
            }

            // If no existing subscription, create new one
            \Stripe\PaymentMethod::retrieve($request->paymentMethodId)->attach([
                'customer' => $user->stripe_id,
            ]);

            $price = \Stripe\Price::retrieve($request->priceId);

            $subscription = \Stripe\Subscription::create([
                'customer'               => $user->stripe_id,
                'items'                  => [['price' => $request->priceId]],
                'default_payment_method' => $request->paymentMethodId,
                'expand'                 => ['latest_invoice.payment_intent'],
            ]);

            $paymentIntent = $subscription->latest_invoice->payment_intent;

            if ($paymentIntent && $paymentIntent->status !== 'succeeded') {
                \Stripe\Subscription::update($subscription->id, ['cancel_at_period_end' => true]);

                return response()->json([
                    'status'  => false,
                    'message' => 'Payment not completed. Please confirm your payment.',
                    'data'    => [
                        'subscriptionId' => $subscription->id,
                        'clientSecret'   => $paymentIntent ? $paymentIntent->client_secret : null,
                        'paymentStatus'  => $paymentIntent ? $paymentIntent->status : 'unknown',
                        'requiresAction' => true,
                    ],
                ], 402);
            }

            $userSubscription = Subscription::create([
                'user_id'       => $user->id,
                'type'          => 'default',
                'stripe_id'     => $subscription->id,
                'stripe_status' => 'active',
                'stripe_price'  => $request->priceId,
                'quantity'      => 1,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Subscription created successfully',
                'data'    => [
                    'subscriptionId' => $subscription->id,
                    'status'         => 'active',
                    'paymentStatus'  => $paymentIntent ? $paymentIntent->status : 'succeeded',
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkSubscriptionStatus(Request $request)
    {
        try {
            $user = auth()->user();

            if (! $user || $user->role !== 'owner') {
                return response()->json([
                    'status'  => false,
                    'message' => 'Only owners can check subscription status',
                ], 403);
            }

            $subscription = Subscription::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if (! $subscription) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No subscription found',
                ], 404);
            }

            Stripe::setApiKey(config('cashier.secret'));
            $stripeSubscription = \Stripe\Subscription::retrieve($subscription->stripe_id);

            return response()->json([
                'status'  => true,
                'message' => 'Subscription status retrieved successfully',
                'data'    => [
                    'subscription_id'    => $subscription->stripe_id,
                    'status'             => $subscription->stripe_status,
                    'is_active'          => $subscription->stripe_status === 'active',
                    'current_period_end' => $stripeSubscription->current_period_end,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function retrySubscriptionPayment(Request $request)
    {
        try {
            $user = auth()->user();

            if (! $user || $user->role !== 'owner') {
                return response()->json([
                    'status'  => false,
                    'message' => 'Only owners can retry subscription payments',
                ], 403);
            }

            $request->validate([
                'subscriptionId'  => 'required|string',
                'paymentMethodId' => 'required|string',
            ]);

            Stripe::setApiKey(config('cashier.secret'));

            $subscription = \Stripe\Subscription::retrieve($request->subscriptionId, [
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            if ($subscription->customer !== $user->stripe_id) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Subscription not found',
                ], 404);
            }

            \Stripe\PaymentMethod::retrieve($request->paymentMethodId)->attach([
                'customer' => $user->stripe_id,
            ]);

            $latestInvoiceId = $subscription->latest_invoice;
            if ($latestInvoiceId) {
                $invoice = \Stripe\Invoice::retrieve($latestInvoiceId);

                if ($invoice->status === 'open') {
                    $invoice = \Stripe\Invoice::pay($latestInvoiceId, [
                        'payment_method' => $request->paymentMethodId,
                        'forgive'        => false,
                    ]);

                    if ($invoice->payment_intent) {
                        $paymentIntent = \Stripe\PaymentIntent::retrieve($invoice->payment_intent);

                        if ($paymentIntent->status === 'succeeded') {
                            $userSubscription = Subscription::where('stripe_id', $request->subscriptionId)->first();
                            if ($userSubscription) {
                                $userSubscription->stripe_status = 'active';
                                $userSubscription->save();
                            }

                            return response()->json([
                                'status'  => true,
                                'message' => 'Payment completed successfully',
                                'data'    => [
                                    'subscriptionId' => $subscription->id,
                                    'status'         => $subscription->status,
                                    'paymentStatus'  => $paymentIntent->status,
                                ],
                            ]);
                        }

                        return response()->json([
                            'status'  => false,
                            'message' => 'Payment requires confirmation',
                            'data'    => [
                                'subscriptionId' => $subscription->id,
                                'clientSecret'   => $paymentIntent->client_secret,
                                'paymentStatus'  => $paymentIntent->status,
                                'requiresAction' => true,
                                'nextAction'     => $paymentIntent->next_action ?? null,
                            ],
                        ], 402);
                    }
                }
            }

            $newInvoice = \Stripe\Invoice::create([
                'customer'          => $user->stripe_id,
                'subscription'      => $request->subscriptionId,
                'auto_advance'      => true,
                'collection_method' => 'charge_automatically',
            ]);

            $newInvoice = \Stripe\Invoice::finalizeInvoice($newInvoice->id, [
                'auto_advance' => true,
            ]);

            $newInvoice = \Stripe\Invoice::pay($newInvoice->id, [
                'payment_method' => $request->paymentMethodId,
                'forgive'        => false,
            ]);

            if ($newInvoice->payment_intent) {
                $paymentIntent = \Stripe\PaymentIntent::retrieve($newInvoice->payment_intent);

                if ($paymentIntent->status === 'succeeded') {
                    $userSubscription = Subscription::where('stripe_id', $request->subscriptionId)->first();
                    if ($userSubscription) {
                        $userSubscription->stripe_status = 'active';
                        $userSubscription->save();
                    }

                    return response()->json([
                        'status'  => true,
                        'message' => 'Payment completed successfully',
                        'data'    => [
                            'subscriptionId' => $subscription->id,
                            'status'         => $subscription->status,
                            'paymentStatus'  => $paymentIntent->status,
                        ],
                    ]);
                }

                return response()->json([
                    'status'  => false,
                    'message' => 'Payment requires confirmation',
                    'data'    => [
                        'subscriptionId' => $subscription->id,
                        'clientSecret'   => $paymentIntent->client_secret,
                        'paymentStatus'  => $paymentIntent->status,
                        'requiresAction' => true,
                        'nextAction'     => $paymentIntent->next_action ?? null,
                    ],
                ], 402);
            }
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function syncSubscriptionStatus(Request $request)
    {
        try {
            $user = auth()->user();

            if (! $user || $user->role !== 'owner') {
                return response()->json([
                    'status'  => false,
                    'message' => 'Only owners can sync subscription status',
                ], 403);
            }

            $subscription = Subscription::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if (! $subscription) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No subscription found',
                ], 404);
            }

            Stripe::setApiKey(config('cashier.secret'));

            $stripeSubscription = \Stripe\Subscription::retrieve($subscription->stripe_id);

            $subscription->stripe_status = $stripeSubscription->status;
            $subscription->ends_at = $stripeSubscription->cancel_at_period_end ?
                \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end) : null;
            $subscription->save();

            $subscriptionDetails = getSubscriptionDetails($user);

            return response()->json([
                'status'  => true,
                'message' => 'Subscription status synced successfully',
                'data'    => [
                    'subscription_id' => $subscription->stripe_id,
                    'status'          => $subscription->stripe_status,
                    'is_active'       => $subscriptionDetails['is_active'] ?? false,
                    'payment_status'  => $subscriptionDetails['stripe']['payment_status'] ?? 'unknown',
                    'refund_status'   => $subscriptionDetails['stripe']['refund_status'] ?? 'unknown',
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function completePayment(Request $request)
    {
        try {
            $user = auth()->user();

            if (! $user || $user->role !== 'owner') {
                return response()->json([
                    'status'  => false,
                    'message' => 'Only owners can complete payments',
                ], 403);
            }

            $request->validate([
                'subscriptionId' => 'required|string',
            ]);

            Stripe::setApiKey(config('cashier.secret'));

            $subscription = \Stripe\Subscription::retrieve($request->subscriptionId);

            $latestInvoiceId = $subscription->latest_invoice;
            if ($latestInvoiceId) {
                $invoice = \Stripe\Invoice::retrieve($latestInvoiceId);

                if ($invoice->status === 'open') {
                    $invoice = \Stripe\Invoice::pay($latestInvoiceId);
                }
            }

            $userSubscription = Subscription::where('stripe_id', $request->subscriptionId)
                ->where('user_id', $user->id)
                ->first();

            if ($userSubscription) {
                $userSubscription->stripe_status = 'active';
                $userSubscription->ends_at = null;
                $userSubscription->save();
            }

            return response()->json([
                'status'  => true,
                'message' => 'Payment completion and subscription activated successfully',
                'data'    => [
                    'subscriptionId' => $request->subscriptionId,
                    'status'         => 'active',
                    'redirect'       => '/owner',
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSubscriptionData(Request $request)
    {
        try {
            $user = auth()->user();

            if (! $user || $user->role !== 'owner') {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthorized access',
                ], 403);
            }

            // Get current subscription details
            $currentSubscription = getSubscriptionDetails($user);
            $currentPlan = null;

            if ($currentSubscription) {
                $createdAt = $currentSubscription['database']['created_at'] instanceof \Carbon\Carbon
                    ? $currentSubscription['database']['created_at']->timestamp
                    : strtotime($currentSubscription['database']['created_at']);

                $periodStart = $currentSubscription['stripe']['current_period_start'] ?: $createdAt;
                $periodEnd = $currentSubscription['stripe']['current_period_end'] ?: ($createdAt + (90 * 24 * 60 * 60));

                // Get actual plan name from Stripe
                $planName = 'Professional Plan';
                $amount = '12.00';
                $interval = '3 months';

                try {
                    Stripe::setApiKey(config('cashier.secret'));
                    $priceId = $currentSubscription['database']['price_id'];
                    if ($priceId) {
                        $price = \Stripe\Price::retrieve($priceId);
                        $product = \Stripe\Product::retrieve($price->product);

                        $planName = $product->name ?: 'Professional Plan';
                        $amount = number_format($price->unit_amount / 100, 2);

                        // Format interval for better readability
                        $intervalCount = $price->recurring->interval_count;
                        $interval = $price->recurring->interval;

                        if ($intervalCount == 1) {
                            $interval = 'per ' . $interval;
                        } else {
                            $interval = 'every ' . $intervalCount . ' ' . $interval . 's';
                        }
                    }
                } catch (Exception $stripeException) {
                    // Keep default values if Stripe fails
                }

                $currentPlan = [
                    'plan_name'         => $planName,
                    'status'            => $currentSubscription['is_active'] ? 'active' : 'inactive',
                    'amount'            => $amount,
                    'interval'          => $interval,
                    'next_billing_date' => $currentSubscription['stripe']['current_period_end'] ?
                        date('M j, Y', $currentSubscription['stripe']['current_period_end']) : 'No billing date',
                    'payment_status'       => $currentSubscription['stripe']['payment_status'],
                    'subscription_id'      => $currentSubscription['database']['stripe_id'],
                    'price_id'             => $currentSubscription['database']['price_id'],
                    'created_at'           => $createdAt,
                    'current_period_start' => $periodStart,
                    'current_period_end'   => $periodEnd,
                    'is_active'            => $currentSubscription['is_active'],
                ];
            }

            // Get all user subscriptions from database
            $subscriptions = $user->subscriptions()
                ->orderBy('created_at', 'desc')
                ->get();

            $recentSubscriptions = [];

            foreach ($subscriptions as $sub) {
                try {
                    // Get Stripe subscription details
                    $stripeSub = \Stripe\Subscription::retrieve($sub->stripe_id);

                    // Get actual plan details from price
                    $planName = 'Professional Plan';
                    $amount = '12.00';
                    $interval = '3 months';

                    try {
                        if ($sub->stripe_price) {
                            $price = \Stripe\Price::retrieve($sub->stripe_price);
                            $product = \Stripe\Product::retrieve($price->product);

                            $planName = $product->name ?: 'Professional Plan';
                            $amount = number_format($price->unit_amount / 100, 2);

                            // Format interval for better readability
                            $intervalCount = $price->recurring->interval_count;
                            $interval = $price->recurring->interval;

                            if ($intervalCount == 1) {
                                $interval = 'per ' . $interval;
                            } else {
                                $interval = 'every ' . $intervalCount . ' ' . $interval . 's';
                            }
                        }
                    } catch (Exception $priceException) {
                        // Keep default values if price retrieval fails
                    }

                    $recentSubscriptions[] = [
                        'id'                   => $sub->id,
                        'stripe_id'            => $sub->stripe_id,
                        'plan_name'            => $planName,
                        'status'               => $stripeSub->status,
                        'amount'               => $amount,
                        'interval'             => $interval,
                        'current_period_start' => $stripeSub->current_period_start,
                        'current_period_end'   => $stripeSub->current_period_end,
                        'created_at'           => $sub->created_at->timestamp,
                        'cancel_at_period_end' => $stripeSub->cancel_at_period_end,
                        'ended_at'             => $stripeSub->ended_at,
                        'canceled_at'          => $stripeSub->canceled_at,
                    ];
                } catch (Exception $stripeException) {
                    // If Stripe subscription doesn't exist, use database data
                    $createdAt = $sub->created_at->timestamp;
                    $endsAt = $sub->ends_at ? $sub->ends_at->timestamp : ($createdAt + (90 * 24 * 60 * 60)); // Default 3 months from creation

                    $planDetails = getPlanDetailsFromPriceId($sub->stripe_price);
                    $planName = $planDetails['name'];
                    $amount = $planDetails['amount'];
                    $interval = $planDetails['interval'];

                    $recentSubscriptions[] = [
                        'id'                   => $sub->id,
                        'stripe_id'            => $sub->stripe_id,
                        'plan_name'            => $planName,
                        'status'               => $sub->stripe_status,
                        'amount'               => $amount,
                        'interval'             => $interval,
                        'current_period_start' => $createdAt,
                        'current_period_end'   => $endsAt,
                        'created_at'           => $createdAt,
                        'cancel_at_period_end' => false,
                        'ended_at'             => null,
                        'canceled_at'          => null,
                    ];
                }
            }

            return response()->json([
                'status'  => true,
                'message' => 'Subscription data retrieved successfully',
                'data'    => [
                    'current_plan'         => $currentPlan,
                    'recent_subscriptions' => $recentSubscriptions,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve subscription data: ' . $e->getMessage(),
                'data'    => [
                    'current_plan'         => null,
                    'recent_subscriptions' => [],
                ],
            ], 500);
        }
    }

    public function cancelSubscription(Request $request)
    {
        try {
            $user = auth()->user();

            if (! $user || $user->role !== 'owner') {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthorized access',
                ], 403);
            }

            $subscription = getSubscriptionDetails($user);

            if (! $subscription || ! $subscription['is_active']) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No active subscription to cancel',
                ], 400);
            }

            // Cancel subscription in Stripe
            $stripeSubscription = \Stripe\Subscription::update(
                $subscription['database']['stripe_id'],
                ['cancel_at_period_end' => true],
            );

            // Update database
            $dbSubscription = Subscription::where('stripe_id', $subscription['database']['stripe_id'])->first();
            if ($dbSubscription) {
                $dbSubscription->stripe_status = 'canceled';
                $dbSubscription->save();
            }

            return response()->json([
                'status'  => true,
                'message' => 'Subscription will be canceled at the end of the billing period',
                'data'    => [
                    'canceled_at' => $stripeSubscription->canceled_at,
                    'ends_at'     => $stripeSubscription->current_period_end,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to cancel subscription: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    public function getAdminSubscriptions(Request $request)
    {
        try {
            $user = auth()->user();

            if (! $user || $user->role !== 'admin') {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthorized access',
                ], 403);
            }

            // Get all subscriptions with owner information
            $subscriptions = Subscription::with('user')
                ->orderBy('created_at', 'desc')
                ->get();

            $adminSubscriptions = [];

            foreach ($subscriptions as $sub) {
                $owner = $sub->user;
                $name = $sub->user->firstName . ' ' . $sub->user->lastName;

                try {
                    // Get Stripe subscription details
                    $stripeSub = \Stripe\Subscription::retrieve($sub->stripe_id);

                    // Get actual plan details from price
                    $planName = 'Professional Plan';
                    $amount = '12.00';
                    $interval = '3 months';

                    try {
                        if ($sub->stripe_price) {
                            $price = \Stripe\Price::retrieve($sub->stripe_price);
                            $product = \Stripe\Product::retrieve($price->product);

                            $planName = $product->name ?: 'Professional Plan';
                            $amount = number_format($price->unit_amount / 100, 2);

                            // Format interval for better readability
                            $intervalCount = $price->recurring->interval_count;
                            $interval = $price->recurring->interval;

                            if ($intervalCount == 1) {
                                $interval = 'per ' . $interval;
                            } else {
                                $interval = 'every ' . $intervalCount . ' ' . $interval . 's';
                            }
                        }
                    } catch (Exception $priceException) {
                        // Keep default values if price retrieval fails
                    }

                    $adminSubscriptions[] = [
                        'id'                   => $sub->id,
                        'user_id'              => $sub->user_id,
                        'stripe_id'            => $sub->stripe_id,
                        'owner_name'           => $name,
                        'owner_email'          => $owner->email,
                        'plan_name'            => $planName,
                        'status'               => $stripeSub->status,
                        'amount'               => $amount,
                        'interval'             => $interval,
                        'current_period_start' => date('M j, Y', $stripeSub->current_period_start),
                        'current_period_end'   => date('M j, Y', $stripeSub->current_period_end),
                        'created_at'           => date('M j, Y', $sub->created_at->timestamp),
                        'cancel_at_period_end' => $stripeSub->cancel_at_period_end,
                        'ended_at'             => $stripeSub->ended_at ? date('M j, Y', $stripeSub->ended_at) : null,
                        'canceled_at'          => $stripeSub->canceled_at ? date('M j, Y', $stripeSub->canceled_at) : null,
                    ];
                } catch (Exception $stripeException) {
                    // If Stripe subscription doesn't exist, use database data
                    $createdAt = $sub->created_at->timestamp;
                    $endsAt = $sub->ends_at ? $sub->ends_at->timestamp : ($createdAt + (90 * 24 * 60 * 60)); // Default 3 months from creation

                    $planDetails = getPlanDetailsFromPriceId($sub->stripe_price);
                    $planName = $planDetails['name'];
                    $amount = $planDetails['amount'];
                    $interval = $planDetails['interval'];
                    $adminSubscriptions[] = [
                        'id'                   => $sub->id,
                        'user_id'              => $sub->user_id,
                        'stripe_id'            => $sub->stripe_id,
                        'owner_name'           => $name,
                        'owner_email'          => $owner->email,
                        'plan_name'            => $planName,
                        'status'               => $sub->stripe_status ?: 'unknown',
                        'amount'               => $amount,
                        'interval'             => $interval,
                        'current_period_start' => date('M j, Y', $createdAt),
                        'current_period_end'   => date('M j, Y', $endsAt),
                        'created_at'           => date('M j, Y', $createdAt),
                        'cancel_at_period_end' => false,
                        'ended_at'             => null,
                        'canceled_at'          => null,
                    ];
                }
            }

            return response()->json([
                'status'  => true,
                'message' => '',
                'data'    => $adminSubscriptions,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve admin subscriptions: ' . $e->getMessage(),
                'data'    => [],
            ], 500);
        }
    }

    public function cancelAdminSubscription(Request $request, $subscriptionId)
    {
        try {
            $user = auth()->user();

            if (! $user || $user->role !== 'admin') {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthorized access',
                ], 403);
            }

            $subscription = Subscription::find($subscriptionId);

            if (! $subscription) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Subscription not found',
                ], 404);
            }

            // Cancel subscription in Stripe
            $stripeSubscription = \Stripe\Subscription::update(
                $subscription->stripe_id,
                ['cancel_at_period_end' => true],
            );

            // Update database
            $subscription->stripe_status = 'canceled';
            $subscription->save();

            return response()->json([
                'status'  => true,
                'message' => 'Subscription canceled successfully',
                'data'    => [
                    'canceled_at' => $stripeSubscription->canceled_at,
                    'ends_at'     => $stripeSubscription->current_period_end,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to cancel subscription: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    public function reactivateSubscription(Request $request)
    {
        try {
            $user = auth()->user();

            if (! $user || $user->role !== 'owner') {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthorized access',
                ], 403);
            }

            $subscriptionId = $request->subscriptionId;

            if (! $subscriptionId) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Subscription ID is required',
                ], 400);
            }

            // Reactivate subscription in Stripe
            $stripeSubscription = \Stripe\Subscription::update(
                $subscriptionId,
                ['cancel_at_period_end' => false],
            );

            // Update database
            $dbSubscription = Subscription::where('stripe_id', $subscriptionId)->first();
            if ($dbSubscription) {
                $dbSubscription->stripe_status = 'active';
                $dbSubscription->ends_at = null;
                $dbSubscription->save();
            }

            return response()->json([
                'status'  => true,
                'message' => 'Subscription reactivated successfully',
                'data'    => [
                    'subscription_id' => $stripeSubscription->id,
                    'status'          => $stripeSubscription->status,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to reactivate subscription: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }
}
