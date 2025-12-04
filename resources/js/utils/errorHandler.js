const formatValidationErrors = errors => {
    if (!errors || typeof errors !== 'object') {
        return 'An unknown error occurred';
    }

    const messages = Object.entries(errors).map(([field, fieldErrors]) => {
        const readableField = field
            .split('_')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');

        const errorMessages = Array.isArray(fieldErrors) ? fieldErrors : [fieldErrors];
        return `${readableField}: ${errorMessages.join(', ')}`;
    });

    return messages.join('\n');
};

const isValidationError = error => {
    return (
        error?.response?.data?.message === 'Validation failed' &&
        error?.response?.data?.errors &&
        typeof error?.response?.data?.errors === 'object'
    );
};

export { formatValidationErrors, isValidationError };
