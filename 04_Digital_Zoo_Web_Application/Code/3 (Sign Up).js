//GATHERING ALL THE VARIABLES WITH THEIR RESPECTIVE ID'S
form=document.getElementById('form');
fname=document.getElementById('fname');
lname=document.getElementById('lname');
dob=document.getElementById('dob');
phoneNumber=document.getElementById('phoneNumber');
email=document.getElementById('email');
username=document.getElementById('username');
password=document.getElementById('password');
confirmPass=document.getElementById('confirmPass');
const option1=document.getElementById('option1');
const option2=document.getElementById('option2');
const option3=document.getElementById('option3');
const securitySelect = document.getElementById('security');
//SECURITY QUESTION CODE
function DefaultHide() {
    option1.style.display='none';
    option2.style.display='none';
    option3.style.display='none';
}
//Adding EventListener for the 'DefaultHide()' function
document.addEventListener('DOMContentLoaded', DefaultHide);
//Displaying and hinding questions depending on the selected option
securitySelect.addEventListener('change', function() {
    const selectedValue = this.value;
    //Hide all questions
    option1.style.display='none';
    option2.style.display = 'none';
    option3.style.display = 'none';
    // Show selected question based on option text
    if (this.selectedIndex === 1) option1.style.display='block';
    if (this.selectedIndex === 2) option2.style.display='block';
    if (this.selectedIndex === 3) option3.style.display='block';
});
//FIRST NAME CODE
fname.addEventListener('input', function(event) {
    //The regular expression /[a-zA-Z]/g matches all uppercase and lowercase letters.
    //The replace method removes any character that is NOT an alphabet (using ^ inside the brackets).
    event.target.value = event.target.value.replace(/[^a-zA-Z]/g, '');
});
//LAST NAME CODE
lname.addEventListener('input', function(event) {
    //The regular expression /[a-zA-Z]/g matches all uppercase and lowercase letters.
    //The replace method removes any character that is NOT an alphabet (using ^ inside the brackets).
    event.target.value = event.target.value.replace(/[^a-zA-Z]/g, '');
});
//USERNAME CODE
username.addEventListener('input', function(event) {
    //The regular expression /[a-zA-Z]/g matches all uppercase and lowercase letters.
    //The replace method removes any character that is NOT an alphabet (using ^ inside the brackets).
    event.target.value = event.target.value.replace(/[^a-zA-Z]/g, '');
});
//CONFIRM PASSWORD CODE AND PASSWORD CODE USING NESTED IF AND ELSE STATEMENTS
form.addEventListener('submit', function(event){
    event.preventDefault();
    const hasUpper = /[A-Z]/.test(password.value);
    const hasLower = /[a-z]/.test(password.value);
    const hasSpecial = /[!@#$%^&*(),.?:;{}|<>]/.test(password.value);
    const hasNum = /[0-9]/.test(password.value);
    if (fname.value && lname.value && dob.value && email.value && 
        username.value && password.value && confirmPass.value) {
        if (password.value === confirmPass.value) {
            if (hasUpper && hasLower && hasSpecial && hasNum) {
                alert(`You ${username.value} have successfully registered as a Digital Zoo member!!!`);
            } else {
                alert("Password must include: Upper/lower case letters, numbers, and special characters!!!");
            }
        } else {
            alert("Passwords do not match!!!");
        }
    } else {
        alert("Please fill in all required fields!!!");
    }
});