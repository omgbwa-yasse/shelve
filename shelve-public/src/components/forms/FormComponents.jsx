import React from 'react';
import PropTypes from 'prop-types';

export const Input = ({
  label,
  error,
  required = false,
  className = '',
  ...props
}) => {
  const inputId = props.id || `input-${Math.random().toString(36).substr(2, 9)}`;

  return (
    <div className={`form-field ${className}`}>
      {label && (
        <label
          htmlFor={inputId}
          className="block text-sm font-medium text-gray-700 mb-2"
        >
          {label}
          {required && <span className="text-red-500 ml-1">*</span>}
        </label>
      )}

      <input
        {...props}
        id={inputId}
        className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
          error ? 'border-red-500' : 'border-gray-300'
        } ${props.className || ''}`}
      />

      {error && (
        <p className="text-red-500 text-sm mt-1" role="alert">
          {error}
        </p>
      )}
    </div>
  );
};

Input.propTypes = {
  label: PropTypes.string,
  error: PropTypes.string,
  required: PropTypes.bool,
  className: PropTypes.string,
  id: PropTypes.string
};

export const TextArea = ({
  label,
  error,
  required = false,
  className = '',
  rows = 4,
  ...props
}) => {
  const textareaId = props.id || `textarea-${Math.random().toString(36).substr(2, 9)}`;

  return (
    <div className={`form-field ${className}`}>
      {label && (
        <label
          htmlFor={textareaId}
          className="block text-sm font-medium text-gray-700 mb-2"
        >
          {label}
          {required && <span className="text-red-500 ml-1">*</span>}
        </label>
      )}

      <textarea
        {...props}
        id={textareaId}
        rows={rows}
        className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 resize-vertical ${
          error ? 'border-red-500' : 'border-gray-300'
        } ${props.className || ''}`}
      />

      {error && (
        <p className="text-red-500 text-sm mt-1" role="alert">
          {error}
        </p>
      )}
    </div>
  );
};

TextArea.propTypes = {
  label: PropTypes.string,
  error: PropTypes.string,
  required: PropTypes.bool,
  className: PropTypes.string,
  rows: PropTypes.number,
  id: PropTypes.string
};

export const Select = ({
  label,
  error,
  required = false,
  options = [],
  placeholder = 'Sélectionner...',
  className = '',
  ...props
}) => {
  const selectId = props.id || `select-${Math.random().toString(36).substr(2, 9)}`;

  return (
    <div className={`form-field ${className}`}>
      {label && (
        <label
          htmlFor={selectId}
          className="block text-sm font-medium text-gray-700 mb-2"
        >
          {label}
          {required && <span className="text-red-500 ml-1">*</span>}
        </label>
      )}

      <select
        {...props}
        id={selectId}
        className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
          error ? 'border-red-500' : 'border-gray-300'
        } ${props.className || ''}`}
      >
        {placeholder && (
          <option value="">{placeholder}</option>
        )}
        {options.map(option => (
          <option
            key={option.value}
            value={option.value}
          >
            {option.label}
          </option>
        ))}
      </select>

      {error && (
        <p className="text-red-500 text-sm mt-1" role="alert">
          {error}
        </p>
      )}
    </div>
  );
};

Select.propTypes = {
  label: PropTypes.string,
  error: PropTypes.string,
  required: PropTypes.bool,
  options: PropTypes.arrayOf(PropTypes.shape({
    value: PropTypes.string.isRequired,
    label: PropTypes.string.isRequired
  })),
  placeholder: PropTypes.string,
  className: PropTypes.string,
  id: PropTypes.string
};

export const Checkbox = ({
  label,
  error,
  className = '',
  ...props
}) => {
  const checkboxId = props.id || `checkbox-${Math.random().toString(36).substr(2, 9)}`;

  return (
    <div className={`form-field ${className}`}>
      <label
        htmlFor={checkboxId}
        className="flex items-start gap-3 cursor-pointer"
      >
        <input
          {...props}
          type="checkbox"
          id={checkboxId}
          className={`mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500 ${
            error ? 'border-red-500' : ''
          } ${props.className || ''}`}
        />
        {label && (
          <span className="text-sm text-gray-700">{label}</span>
        )}
      </label>

      {error && (
        <p className="text-red-500 text-sm mt-1" role="alert">
          {error}
        </p>
      )}
    </div>
  );
};

Checkbox.propTypes = {
  label: PropTypes.node,
  error: PropTypes.string,
  className: PropTypes.string,
  id: PropTypes.string
};

export const Radio = ({
  label,
  error,
  options = [],
  name,
  value,
  onChange,
  className = '',
  ...props
}) => {
  return (
    <fieldset className={`form-field ${className}`}>
      {label && (
        <legend className="block text-sm font-medium text-gray-700 mb-3">
          {label}
        </legend>
      )}

      <div className="space-y-2">
        {options.map(option => {
          const radioId = `${name}-${option.value}`;
          return (
            <label
              key={option.value}
              htmlFor={radioId}
              className="flex items-center gap-3 cursor-pointer"
            >
              <input
                {...props}
                type="radio"
                id={radioId}
                name={name}
                value={option.value}
                checked={value === option.value}
                onChange={onChange}
                className="text-blue-600 focus:ring-blue-500"
              />
              <span className="text-sm text-gray-700">{option.label}</span>
            </label>
          );
        })}
      </div>

      {error && (
        <p className="text-red-500 text-sm mt-1" role="alert">
          {error}
        </p>
      )}
    </fieldset>
  );
};

Radio.propTypes = {
  label: PropTypes.string,
  error: PropTypes.string,
  options: PropTypes.arrayOf(PropTypes.shape({
    value: PropTypes.string.isRequired,
    label: PropTypes.string.isRequired
  })).isRequired,
  name: PropTypes.string.isRequired,
  value: PropTypes.string,
  onChange: PropTypes.func.isRequired,
  className: PropTypes.string
};

export const Button = ({
  children,
  variant = 'primary',
  size = 'medium',
  disabled = false,
  loading = false,
  className = '',
  ...props
}) => {
  const baseClasses = 'inline-flex items-center justify-center border font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors';

  const variants = {
    primary: 'border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
    secondary: 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:ring-blue-500',
    danger: 'border-transparent bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    success: 'border-transparent bg-green-600 text-white hover:bg-green-700 focus:ring-green-500'
  };

  const sizes = {
    small: 'px-3 py-2 text-sm',
    medium: 'px-4 py-2 text-sm',
    large: 'px-6 py-3 text-base'
  };

  return (
    <button
      {...props}
      disabled={disabled || loading}
      className={`
        ${baseClasses}
        ${variants[variant]}
        ${sizes[size]}
        ${disabled || loading ? 'opacity-50 cursor-not-allowed' : ''}
        ${className}
      `.trim().replace(/\s+/g, ' ')}
    >
      {loading && (
        <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-current" fill="none" viewBox="0 0 24 24">
          <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
          <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      )}
      {children}
    </button>
  );
};

Button.propTypes = {
  children: PropTypes.node.isRequired,
  variant: PropTypes.oneOf(['primary', 'secondary', 'danger', 'success']),
  size: PropTypes.oneOf(['small', 'medium', 'large']),
  disabled: PropTypes.bool,
  loading: PropTypes.bool,
  className: PropTypes.string
};

export const SearchInput = ({
  value,
  onChange,
  onSearch,
  placeholder = 'Rechercher...',
  suggestions = [],
  showSuggestions = false,
  onSuggestionClick,
  className = '',
  ...props
}) => {
  const inputId = props.id || `search-${Math.random().toString(36).substr(2, 9)}`;

  return (
    <div className={`relative ${className}`}>
      <div className="relative">
        <input
          {...props}
          id={inputId}
          type="search"
          value={value}
          onChange={onChange}
          placeholder={placeholder}
          className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg className="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>

        {onSearch && (
          <button
            type="button"
            onClick={onSearch}
            className="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
          >
            <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 7l5 5-5 5M6 12h12" />
            </svg>
          </button>
        )}
      </div>

      {showSuggestions && suggestions.length > 0 && (
        <div className="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">          {suggestions.map((suggestion) => (
            <button
              key={suggestion}
              type="button"
              onClick={() => onSuggestionClick && onSuggestionClick(suggestion)}
              className="w-full px-4 py-2 text-left hover:bg-gray-50 first:rounded-t-md last:rounded-b-md"
            >
              {suggestion}
            </button>
          ))}
        </div>
      )}
    </div>
  );
};

SearchInput.propTypes = {
  value: PropTypes.string.isRequired,
  onChange: PropTypes.func.isRequired,
  onSearch: PropTypes.func,
  placeholder: PropTypes.string,
  suggestions: PropTypes.arrayOf(PropTypes.string),
  showSuggestions: PropTypes.bool,
  onSuggestionClick: PropTypes.func,
  className: PropTypes.string,
  id: PropTypes.string
};
