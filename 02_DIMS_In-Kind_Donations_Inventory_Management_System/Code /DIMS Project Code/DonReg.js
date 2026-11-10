//Using Email JS to send the self verification email
//This initialises EmailJS with my public key and tells the EmailJS library which account to use when sending emails from the browser. This must be called before using the 'emailjs.send()' function 
emailjs.init("fhgdeTJ-xBNbz1hW_");
function handleEmail () {
    const form=document.getElementById('form');
    //When the form is submitted:
    form.addEventListener("submit", function (event) {
        //This will prevent full page reloads, thus a loss of data
        event.preventDefault();
        //This collects all the form fields
        const formData=new FormData(form);
        //This sends a HTTP 'POST' request to the "DonRegForm.php" file, and sends the server the submitted form fields
        fetch("DonRegForm.php", {method: "POST", body: formData})
        //When the server replies, this line tries to convert the server’s response into JSON so JavaScript can use it as an object.
        // Parsing is the process of turning one form of data (usually a string of text or numbers or whatever) into a data structure.
        .then(response=>response.json())
        //Now 'data' represents the JSON object returned from the server, and if the if statement (if (!data.success)) returns true, that means the server found a problem. 
        //The return statement stops the rest of the process and prevents the email from sending.
        .then(data=>{
            console.log("DEBUG DATA:", data);
            if (!data.success) {
                alert(data.message);
                return;
            }
        //Basically, this code sends the form data to the server, then tries to translate the response to JSON format and uses that to create the data object that will send the email. 
        //Now that the object is returned to the server, if there is an error, it will terminate sending the email.
            const email=data.email;
            const username=data.username;
            const token=data.token;
            //These store the three properties returned from the server's response as constants.
            const verificationLink="http://localhost/DIMS_FINAL/System/DonRegVerification.php?email="+encodeURIComponent(email)+"&token=" +encodeURIComponent(token);
            //The 'verificationLink' makes a URL using the 'encodeURIComponent()' to convert the "email" and "token" values into URL-safe values.
            //This will prevent errors when the email contains special characters. 
            //And without encoding, the link could break, and php will not receive the correct data (email or token).
            emailjs.send(
                "service_q5kugqo",
                "template_vpahfla",
                {
                    to_email: email,
                    to_username: username,
                    verification_link: verificationLink
                }
            )
            //This will send an email through EmailJS and will supply the service and template ID.
            //It will map the 'data' object values into the template placeholders (shown in the curly braces).
            //And this function will add the values into the template and send the email
            //emailjs. returns a promise, so you can handle success or failure using '.then()' and '.catch()' respectively. 
            //A promise is an object that links producing code (code that can take some time) and consuming code (code that must wait for the result). 
            //It can handle outcomes of success or failure by providing methods to attach handlers for the following eventual outcomes: .then(), .catch(), .finally()
            .then(() => {
                alert("Registration successful! Please check your email to verify your account.");
                form.reset();
            })
            //If 'emailjs.send' is successful, then it will use the '.then()' function and display a success message, and it will reset all form fields using the "form.reset()" function.
            .catch((error) => {
                console.error("EmailJS Error:", error);
                alert("Your account was created, but we could NOT send the verification email. Please notify admin.");
            });
            //If 'emailjs.send()' fails (depending on network error, or incorrect IDs), the '.catch()' function will log the error to the console for debugging. 
            //And it will display the message that the account was created, but the verification email failed to send.  
        })
        //This closes off the second then function at the beginning that handled the parsed server response
        .catch(error => {
            console.error("PHP/Server Error:", error);
            alert("Something went wrong. Please try again.");
        });
        //This catch function will run when the fetch function above fails (fetching the submitted form fields for the server).
        //It will also send the error to the console for debugging and will display the error message.
    });
}
document.addEventListener("DOMContentLoaded", handleEmail)
//When the page loads it will execute the 'handleEmail()' function