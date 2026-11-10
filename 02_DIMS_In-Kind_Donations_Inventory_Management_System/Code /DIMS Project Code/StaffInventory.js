//GENERATE QR CODE
document.querySelectorAll(".generateQR").forEach(btn => {
    btn.addEventListener("click", function () {
        const id = this.dataset.id;
        let formData = new FormData();
        formData.append("inventory_id", id);
        fetch("GenerateQR.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === "QR_GENERATED") {
                alert("QR Code Generated Successfully!");
            } else {
                alert("QR Generation Failed: " + data);
            }
            location.reload();
        })
        .catch(err => {
            console.error(err);
            alert("Error generating QR code.");
        });
    });
});


//MARK ITEM AS DISTRIBUTED
document.querySelectorAll(".distributeBtn").forEach(btn => {
    btn.addEventListener("click", function () {
        const id = this.dataset.id;
        if (!confirm("Mark this item as distributed?")) return;
        let formData = new FormData();
        formData.append("inventory_id", id);
        fetch("MarkDistributed.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === "OK") {
                alert("Item marked as Distributed!");
            } else {
                alert("Error: " + data);
            }
            location.reload();
        })
        .catch(err => {
            console.error(err);
            alert("Error updating item status.");
        });
    });
});
