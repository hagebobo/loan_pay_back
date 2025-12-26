<?php
session_start();

if(isset($_SESSION['user'])){
    header('location:loanpayback.php');
    exit();
}

include('connection.php');

$error_message = '';

if(isset($_POST['login'])){
    $username = mysqli_real_escape_string($con, trim($_POST['username']));
    $password = trim($_POST['password']);

    if(!empty($username) && !empty($password)){
        $sql = "SELECT * FROM users WHERE username='$username'";
        $query = mysqli_query($con,$sql);

        if(mysqli_num_rows($query) > 0){
            $row = mysqli_fetch_assoc($query);
            if(password_verify($password, $row['password'])){
                $_SESSION['user'] = $row['username'];
                $_SESSION['fullname'] = $row['fullname'];
                header('location:loanpayback.php');
                exit();
            } else {
                $error_message = "Invalid username or password";
            }
        } else {
            $error_message = "Invalid username or password";
        }
    } else {
        $error_message = "Please fill all fields";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Loan Payback Prediction System</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet"
 href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

<style>
/* ===== RESET ===== */
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

/* ===== BODY BACKGROUND ===== */
body{
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    font-family:'Outfit',sans-serif;
    background:linear-gradient(135deg,#f5f7fa,#c3cfe2);
    overflow:hidden;
    position:relative;
}

/* ===== BACKGROUND LAYER ===== */
body::before{
    content:'';
    position:fixed;
    inset:0;
    background:
      radial-gradient(circle at 20% 50%, rgba(255,70,85,.1), transparent 60%),
      radial-gradient(circle at 80% 80%, rgba(0,217,255,.08), transparent 60%),
      radial-gradient(circle at 40% 20%, rgba(45,53,97,.1), transparent 60%);
    z-index:0;
}

/* ===== FLOATING ELEMENTS ===== */
.floating-element{
    position:absolute;
    border-radius:50%;
    filter:blur(90px);
    opacity:.15;
    animation:float 18s ease-in-out infinite;
    z-index:1;
}
.element-1{
    width:350px;height:350px;
    background:#ff4655;
    top:-100px;right:-100px;
}
.element-2{
    width:300px;height:300px;
    background:#00d9ff;
    bottom:-80px;left:-80px;
}
.element-3{
    width:280px;height:280px;
    background:#2d3561;
    top:50%;left:50%;
    transform:translate(-50%,-50%);
}

@keyframes float{
    0%,100%{transform:translate(0,0);}
    50%{transform:translate(40px,-40px);}
}

/* ===== MONEY ANIMATION CONTAINER ===== */
.money-animation{
    position:fixed;
    inset:0;
    z-index:2;
    pointer-events:none;
    overflow:hidden;
}

/* ===== REAL RWANDAN FRANC BANKNOTE IMAGES ===== */
.money-bill{
    position:absolute;
    width:500px;
    height:250px;
    opacity:0;
    animation:fall linear infinite;
    background-size:cover;
    background-position:center;
    background-repeat:no-repeat;
    border-radius:4px;
    box-shadow:0 4px 12px rgba(0,0,0,0.25);
}

/* 
   NOTE: Place your Rwandan Franc banknote images in an 'images' folder
   and name them as follows:
   - rwf-5000.jpg or rwf-5000.png
   - rwf-2000.jpg or rwf-2000.png
   - rwf-1000.jpg or rwf-1000.png
   - rwf-500.jpg or rwf-500.png
   - rwf-100.jpg or rwf-100.png
*/

.bill-5000{
    background-image:url('images/rwf-5000.jpg');
}

.bill-2000{
    background-image:url('images/rwf-2000.jpg');
}

.bill-1000{
    background-image:url('images/rwf-1000.jpg');
}

.bill-500{
    background-image:url('images/rwf-500.jpg');
}

.bill-100{
    background-image:url('images/rwf-100.jpg');
}

@keyframes fall{
    0%{
        opacity:0;
        transform:translateY(-100px) rotate(-15deg);
    }
    10%{
        opacity:0.8;
    }
    90%{
        opacity:0.8;
    }
    100%{
        opacity:0;
        transform:translateY(100vh) rotate(195deg);
    }
}

/* Different animation delays and durations for each bill */
.money-bill:nth-child(1){left:5%;animation-duration:14s;animation-delay:0s;}
.money-bill:nth-child(2){left:15%;animation-duration:16s;animation-delay:2s;}
.money-bill:nth-child(3){left:25%;animation-duration:15s;animation-delay:4s;}
.money-bill:nth-child(4){left:35%;animation-duration:17s;animation-delay:1s;}
.money-bill:nth-child(5){left:45%;animation-duration:18s;animation-delay:3s;}
.money-bill:nth-child(6){left:55%;animation-duration:14s;animation-delay:5s;}
.money-bill:nth-child(7){left:65%;animation-duration:16s;animation-delay:2s;}
.money-bill:nth-child(8){left:75%;animation-duration:15s;animation-delay:4s;}
.money-bill:nth-child(9){left:85%;animation-duration:17s;animation-delay:1s;}
.money-bill:nth-child(10){left:95%;animation-duration:18s;animation-delay:3s;}
.money-bill:nth-child(11){left:10%;animation-duration:15s;animation-delay:6s;}
.money-bill:nth-child(12){left:20%;animation-duration:17s;animation-delay:7s;}
.money-bill:nth-child(13){left:30%;animation-duration:16s;animation-delay:8s;}
.money-bill:nth-child(14){left:40%;animation-duration:14s;animation-delay:5s;}
.money-bill:nth-child(15){left:50%;animation-duration:18s;animation-delay:6s;}
.money-bill:nth-child(16){left:60%;animation-duration:15s;animation-delay:9s;}
.money-bill:nth-child(17){left:70%;animation-duration:17s;animation-delay:10s;}
.money-bill:nth-child(18){left:80%;animation-duration:16s;animation-delay:11s;}

/* ===== LOGIN CONTAINER ===== */
.login-container{
    position:relative;
    z-index:5;
    width:100%;
    max-width:420px;
    padding:20px;
}

/* ===== LOGIN CARD (NO BACKGROUND BLEED) ===== */
.login-card{
    background:#ffffff;
    border-radius:22px;
    padding:40px 35px;
    box-shadow:0 20px 60px rgba(0,0,0,.15);
}

/* ===== LOGO ===== */
.logo{
    width:70px;height:70px;
    margin:0 auto 15px;
    background:linear-gradient(135deg,#ff4655,#00d9ff);
    border-radius:18px;
    display:flex;
    align-items:center;
    justify-content:center;
}
.logo i{
    color:#fff;
    font-size:30px;
}

h1{
    text-align:center;
    margin-bottom:6px;
}
.subtitle{
    text-align:center;
    color:#777;
    margin-bottom:25px;
}

/* ===== ALERT ===== */
.alert{
    background:#ffe5e7;
    border:1px solid #ff4655;
    padding:12px;
    border-radius:10px;
    color:#ff4655;
    margin-bottom:20px;
    font-size:.9rem;
}

/* ===== FORM ===== */
.form-group{margin-bottom:18px;}
label{font-weight:600;font-size:.85rem;}
input{
    width:100%;
    padding:14px;
    margin-top:6px;
    border-radius:10px;
    border:1px solid #ccc;
    font-size:.9rem;
}
input:focus{
    outline:none;
    border-color:#ff4655;
}

/* ===== BUTTON ===== */
.btn-login{
    width:100%;
    padding:14px;
    border:none;
    border-radius:12px;
    background:linear-gradient(135deg,#ff4655,#00d9ff);
    color:#fff;
    font-weight:700;
    cursor:pointer;
    margin-top:10px;
}
.btn-login:hover{opacity:.9}

/* ===== FOOTER ===== */
.footer{
    text-align:center;
    margin-top:18px;
    font-size:.85rem;
}
.footer a{
    color:#00d9ff;
    text-decoration:none;
}
</style>
</head>

<body>

<!-- BACKGROUND ELEMENTS -->
<div class="floating-element element-1"></div>
<div class="floating-element element-2"></div>
<div class="floating-element element-3"></div>

<!-- MONEY ANIMATION WITH REAL RWANDAN FRANC IMAGES -->
<div class="money-animation">
    <div class="money-bill bill-5000"></div>
    <div class="money-bill bill-2000"></div>
    <div class="money-bill bill-1000"></div>
    <div class="money-bill bill-500"></div>
    <div class="money-bill bill-100"></div>
    <div class="money-bill bill-5000"></div>
    <div class="money-bill bill-2000"></div>
    <div class="money-bill bill-1000"></div>
    <div class="money-bill bill-500"></div>
    <div class="money-bill bill-100"></div>
    <div class="money-bill bill-5000"></div>
    <div class="money-bill bill-2000"></div>
    <div class="money-bill bill-1000"></div>
    <div class="money-bill bill-500"></div>
    <div class="money-bill bill-100"></div>
    <div class="money-bill bill-5000"></div>
    <div class="money-bill bill-2000"></div>
    <div class="money-bill bill-1000"></div>
</div>

<!-- LOGIN -->
<div class="login-container">
    <div class="login-card">

        <div class="logo">
            <i class="fa fa-money"></i>
        </div>

        <h1>Welcome Back</h1>
        <p class="subtitle">Loan Payback Prediction System</p>

        <?php if($error_message): ?>
        <div class="alert"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button class="btn-login" name="login">Sign In</button>
        </form>

        <div class="footer">
            Don't have an account?
            <a href="register.php">Register</a>
        </div>

    </div>
</div>

</body>
</html>