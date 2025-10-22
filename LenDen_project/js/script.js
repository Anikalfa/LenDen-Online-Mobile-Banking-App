$(document).ready(function () {
    // User Login
    $("#loginForm").submit(function (e) {
        e.preventDefault();
        let email = $("#email").val().trim();
        let password = $("#password").val().trim();

        if (email === "" || password === "") {
            alert("Please fill in all fields.");
            return;
        }

        $.ajax({
            type: "POST",
            url: "backend/login.php",
            data: { email: email, password: password },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    window.location.href = "dashboard.php";
                } else {
                    alert(response.message);
                }
            },
            error: function () {
                alert("An error occurred. Please try again.");
            }
        });
    });

    // User Registration
    $("#registerForm").submit(function (e) {
        e.preventDefault();
        let name = $("#name").val().trim();
        let email = $("#email").val().trim();
        let phone = $("#phone").val().trim();
        let password = $("#password").val().trim();

        if (name === "" || email === "" || phone === "" || password === "") {
            alert("Please fill in all fields.");
            return;
        }

        $.ajax({
            type: "POST",
            url: "backend/register.php",
            data: { name: name, email: email, phone: phone, password: password },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    alert("Registration Successful!");
                    window.location.href = "login.php";
                } else {
                    alert(response.message);
                }
            },
            error: function () {
                alert("An error occurred. Please try again.");
            }
        });
    });

    $("#sendMoneyForm").submit(function (e) {
        e.preventDefault();
    
        $.ajax({
            type: "POST",
            url: "backend/send_money.php",
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    $("#responseMessage").text(response.message).css("color", "green");
                } else {
                    $("#responseMessage").text(response.message).css("color", "red");
                }
            },
            error: function () {
                $("#responseMessage").text("An error occurred. Please try again.").css("color", "red");
            }
        });
    });
    

    // Load Transaction History
    function loadTransactions() {
        $.ajax({
            url: "backend/transactions.php",
            type: "GET",
            success: function (data) {
                $("#transactionHistory").html(data);
            },
            error: function () {
                $("#transactionHistory").text("Failed to load transactions.");
            }
        });
    }
    loadTransactions();

    // Receive Money
    $("#receiveMoneyForm").submit(function (e) {
        e.preventDefault();
        let sender = $("#sender_phone").val().trim();
        let amount = $("#receive_amount").val().trim();

        if (sender === "" || amount === "") {
            alert("Please fill in all fields.");
            return;
        }

        $.ajax({
            type: "POST",
            url: "backend/receive_money.php",
            data: { sender: sender, amount: amount },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    alert(response.message);
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function () {
                alert("An error occurred. Please try again.");
            }
        });
    });
});
