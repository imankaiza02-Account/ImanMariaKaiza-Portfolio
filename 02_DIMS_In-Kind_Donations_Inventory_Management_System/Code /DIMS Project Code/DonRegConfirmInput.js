//Checking and setting restrictions for input elements
const username = document.getElementById('user-name');
const password = document.getElementById('login-pass');
const phoneNumber = document.getElementById('num');
const form = document.getElementById('form');

//Create password feedback element
const passwordFeedback = document.createElement('div');
passwordFeedback.className = 'password-feedback';
password.parentNode.appendChild(passwordFeedback);

//Username restriction: allow only letters and numbers
username.addEventListener('input', function(event) {
    event.target.value = event.target.value.replace(/[^a-zA-Z0-9]/g, '');
});

//Phone number restriction: allow + only at beginning, then only numbers
phoneNumber.addEventListener('input', function(event) {
    let value = event.target.value;
    
    //If starts with +, keep it and allow only numbers after
    if (value.startsWith('+')) {
        event.target.value = '+' + value.slice(1).replace(/[^0-9]/g, '');
    } else {
        //If doesn't start with +, allow only numbers
        event.target.value = value.replace(/[^0-9]/g, '');
    }
});

//Password strength checker
password.addEventListener('input', function(event) {
    checkPasswordStrength(event.target.value);
});

//Password strength validation function
function checkPasswordStrength(password) {
    const requirements = {
        length: password.length >= 8,
        lowercase: /[a-z]/.test(password),
        uppercase: /[A-Z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
    };

    const missingRequirements = [];
    if (!requirements.length) missingRequirements.push('at least 8 characters');
    if (!requirements.lowercase) missingRequirements.push('a lowercase letter');
    if (!requirements.uppercase) missingRequirements.push('an uppercase letter');
    if (!requirements.number) missingRequirements.push('a number');
    if (!requirements.special) missingRequirements.push('a special character');

    if (missingRequirements.length === 0) {
        passwordFeedback.textContent = 'Strong password';
        passwordFeedback.style.color = 'green';
        return true;
    } else {
        passwordFeedback.textContent = `Password should contain: ${missingRequirements.join(', ')}`;
        passwordFeedback.style.color = 'red';
        return false;
    }
}

//Form submission validation
form.addEventListener('submit', function(event) {
    const isStrongPassword = checkPasswordStrength(password.value);
    
    if (!isStrongPassword) {
        event.preventDefault();
        passwordFeedback.textContent = `Please fix password requirements: ${getMissingRequirements(password.value).join(', ')}`;
        passwordFeedback.style.color = 'red';
        password.focus();
    }
});

//Helper function to get missing password requirements
function getMissingRequirements(password) {
    const requirements = {
        length: password.length >= 8,
        lowercase: /[a-z]/.test(password),
        uppercase: /[A-Z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
    };

    const missing = [];
    if (!requirements.length) missing.push('at least 8 characters');
    if (!requirements.lowercase) missing.push('a lowercase letter');
    if (!requirements.uppercase) missing.push('an uppercase letter');
    if (!requirements.number) missing.push('a number');
    if (!requirements.special) missing.push('a special character');
    
    return missing;
}