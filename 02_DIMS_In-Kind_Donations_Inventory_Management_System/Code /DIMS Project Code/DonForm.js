function donorForm() {
    const addItemBtn=document.getElementById("addItemBtn");
    const itemsList=document.getElementById("itemsList");
    const form=document.getElementById("donationForm");
    const maxItems=10;
    const formMessage=document.getElementById("formMessage");
    const submitBtn=document.getElementById("submitDonation");
    
    //Creating a new item card
    function createItemCard(index) {
        const div=document.createElement("div");
        div.classList.add("item-card");
        div.setAttribute("data-index", index);

        div.innerHTML= 
        `<div class="item-row">
                <label>Item Name</label>
                <input type="text" name="itemName[]" required placeholder="e.g. blankets">
            </div>

            <div class="item-row half">
                <label>Quantity</label>
                <input type="text" name="quantity[]" value="1" required>
            </div>

            <div class="item-row">
                <label>Description</label>
                <textarea name="description[]" rows="2" placeholder="This is optional, but you can include a short description"></textarea>
            </div>

            <div class="item-actions">
                <button type="button" class="removeItemBtn">Remove</button>
            </div>`;

        return div;
    }
    
    // Fix indexes after removing
    function reorderIndexes() {
        const cards=itemsList.querySelectorAll(".item-card");
        cards.forEach((card, i)=>card.setAttribute("data-index", i));
    }
    
    //Showing the message to user
    function showMessage(msg, type="success") {
        if (!msg) {
            formMessage.textContent="";
            formMessage.className="";
            formMessage.style.display="none";
            return;
        }

        formMessage.textContent=msg;
        formMessage.className=type;
        formMessage.style.display="block";
    }

    //Disabling or enabling button during submit
    function setLoading(isLoading) {
        submitBtn.disabled=isLoading;
        submitBtn.innerHTML=isLoading ? '<i class="fas fa-spinner fa-spin"></i> Submitting...' : 'Submit Donation';
    }

    //Validating form before submission
    function validateForm() {
        const itemCount=itemsList.querySelectorAll(".item-card").length;
        
        //Checking if there's at least one item
        if (itemCount===0) {
            showMessage("Please add at least one item before submitting.", "error");
            return false;
        }

        //Checking item count limit
        if (itemCount>maxItems) {
            showMessage(`You cannot submit more than ${maxItems} items.`, "error");
            return false;
        }

        //Checking that all item names are filled
        const itemNames=itemsList.querySelectorAll('input[name="itemName[]"]');
        let hasEmptyName=false;
        itemNames.forEach(function checkName(input) {
            if (!input.value.trim()) {
                input.style.borderColor="#FF6B35";
                hasEmptyName=true;
            } else {
                input.style.borderColor="";
            }
        });
        
        if (hasEmptyName) {
            showMessage("Please fill in all item names.", "error");
            return false;
        }

        //Checking that all quantities are filled
        const quantities=itemsList.querySelectorAll('input[name="quantity[]"]');
        let hasEmptyQuantity=false;
        quantities.forEach(function checkQuantity(input) {
            if (!input.value || input.value==="") {
                input.style.borderColor="#FF6B35";
                hasEmptyQuantity=true;
            } else {
                input.style.borderColor="";
            }
        });
        //The quantity cannot be zero
        let hasZeroQty = false;
        quantities.forEach(input => {
            if (parseInt(input.value) === 0) {
                input.style.borderColor = "#FF6B35";
                hasZeroQty = true;
            }
});

if (hasZeroQty) {
    showMessage("Quantity cannot be 0. Please enter 1 or more.", "error");
    return false;
}

        if (hasEmptyQuantity) {
            showMessage("Please enter a quantity for all items.", "error");
            return false;
        }

        return true;
    }
    
    //Input restrictions
    itemsList.addEventListener("input", function handleInput(e) {
        //Item Name: Only letters (a-z, A-Z), no numbers, no characters, no signs
        if (e.target.name==="itemName[]") {
            e.target.value=e.target.value.replace(/[^a-zA-Z ]/g, '');
        }
        
        //Quantity: Only numbers (0-9), no letters, no characters, no signs, no spaces
        if (e.target.name==="quantity[]") {
            e.target.value=e.target.value.replace(/[^0-9]/g, '');
            
            //Auto-cap at 1000
            if (parseInt(e.target.value)>1000) {
                e.target.value="1000";
                showMessage("Maximum quantity per item is 1000", "error");
                setTimeout(()=>showMessage(""), 2000);
            }
        }
        
        //Description: Allow everything (letters, numbers, characters, signs, spaces, symbols)
        //No restrictions needed for description
    });
    
    //Adding an item
    addItemBtn.addEventListener("click", function addItem() {
        const currentItems=itemsList.querySelectorAll(".item-card").length;

        if (currentItems>=maxItems) {
            showMessage(`You cannot add more than ${maxItems} items.`, "error");
            return;
        }

        const newCard=createItemCard(currentItems);
        itemsList.appendChild(newCard);
        showMessage(""); // Clear any previous messages
    });
    
    //Removing an item
    itemsList.addEventListener("click", function handleRemove(e) {
        if (e.target.classList.contains("removeItemBtn")) {
            const currentItems=itemsList.querySelectorAll(".item-card").length;
            
            //Prevent users from removing the last item
            if (currentItems<=1) {
                showMessage("You must have at least one item.", "error");
                return;
            }
            
            const card=e.target.closest(".item-card");
            card.remove();
            reorderIndexes();
            showMessage(""); // Clear messages after removal
        }
    });

    //Submitting the form via AJAX
    form.addEventListener("submit", async function handleSubmit(e) {
        e.preventDefault();
        
        //Clearing the previous messages
        showMessage("");

        //Validating the form
        if (!validateForm()) {
            return;
        }

        setLoading(true);

        const formData=new FormData(form);

        try {
            const res=await fetch("DonFormProcessor.php", {
                method: "POST",
                body: formData
            });

            const data=await res.json();

            if (data.success) {
                showMessage("✓ Donation submitted successfully!", "success");
                
                //Reseting form after short delay to show success message
                setTimeout(()=> {
                    form.reset();
                    // Keep 1 empty item card
                    itemsList.innerHTML="";
                    itemsList.appendChild(createItemCard(0));
                    showMessage("");
                }, 2000);
            } else {
                showMessage(data.message || "An error occurred. Please try again.", "error");
            }
        } catch (err) {
            showMessage("A system error occurred. Please try again.", "error");
            console.error("Submission error:", err);
        }

        setLoading(false);
    });
}

document.addEventListener("DOMContentLoaded", donorForm);   