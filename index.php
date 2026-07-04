<?php
session_start();


// --- Login credentials ---
$USER = "admin";
$PASS = "Aa112233";

$tokenFile = __DIR__ . '/session_token.txt';

// --- Logout handler ---
if(isset($_GET['logout'])){
    session_destroy();
    header("Location: index.php");
    exit;
}

// --- Login handler ---
if(isset($_POST['login'])){
    $id = $_POST['loginId'] ?? '';
    $pass = $_POST['loginPass'] ?? '';

    if($id === $USER && $pass === $PASS){

        $_SESSION['login'] = true;

        $token = bin2hex(random_bytes(16));
        $_SESSION['token'] = $token;

        file_put_contents($tokenFile, $token);

        header("Location: index.php");
        exit;

    } else {
        $error = "Wrong ID or Password";
    }
}

// --- Auto logout idle 5 menit ---
$timeout_duration = 300;

if(isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration){
    session_destroy();
    header("Location: index.php");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

// --- cek single login device ---
if(isset($_SESSION['login']) && $_SESSION['login'] === true){

    if(file_exists($tokenFile)){

        $serverToken = trim(file_get_contents($tokenFile));

        if(isset($_SESSION['token']) && $_SESSION['token'] !== $serverToken){
            session_destroy();
            header("Location: index.php");
            exit;
        }

    }

}

// --- Check login ---
$loggedIn = isset($_SESSION['login']) && $_SESSION['login'] === true;
?>
<!DOCTYPE html>
<html>
<head>
<title>TREZOR</title>
<link rel="icon" href="https://ik.imagekit.io/hogkfuytk/5968260.png">
<style>
body{
background-color:#000;
background-size:contain;
background-position:center;
background-repeat:no-repeat;

color:white;
font-family:Arial;
text-align:center;
}
h1{margin-top:20px;}
.container{
  max-width:1100px;
  width:95%;
  margin:auto;
}.card{background:#1e293b;padding:20px;margin:15px;border-radius:10px;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{padding:10px;border-bottom:1px solid #334155;}
input,select{padding:8px;margin:6px;}
button{padding:8px 14px;cursor:pointer;background:#2563eb;border:none;color:white;border-radius:5px;}

.logout{
background:#ef4444;
position:absolute;
top:20px;
right:20px;
}

.header{
position:relative;
text-align:center;
}

.profit{color:#22c55e;}
.loss{color:#ef4444;}
.pricebox{display:flex;justify-content:center;gap:40px;font-size:18px;}
#loginBox{margin-top:0px;}
</style>
</head>
<body>

<?php if(!$loggedIn): ?>

<style>
body{
  margin:0;
  height:100vh;
  display:flex;
  justify-content:center;
  align-items:center;
  font-family:'Inter',sans-serif;
  background: radial-gradient(circle at top, #0f172a, #020617);
  overflow:hidden;
}

/* CARD */
.login-card{
  width:340px;
  padding:40px 30px;
  border-radius:20px;
  background:rgba(255,255,255,0.05);
  backdrop-filter:blur(20px);
  border:1px solid rgba(255,255,255,0.1);
  box-shadow:0 0 40px rgba(59,130,246,0.2);
  text-align:center;
}

/* ICON */
.crypto-icons{
  display:flex;
  justify-content:center;
  gap:15px;
  margin-bottom:20px;
}
.crypto-icons img{
  width:40px;
}

/* TITLE */
.title{
  font-size:26px;
  font-weight:700;
  margin-bottom:5px;
}
.subtitle{
  font-size:14px;
  color:#94a3b8;
  margin-bottom:25px;
}

/* INPUT */
.input-box{
  width:90%;
  padding:12px 15px;
  margin:10px 0;
  border-radius:12px;
  border:none;
  background:rgba(255,255,255,0.08);
  color:white;
}

/* BUTTON */
.btn-login{
  width:100%;
  padding:14px;
  margin-top:15px;
  border:none;
  border-radius:12px;
  background:linear-gradient(135deg,#3b82f6,#1d4ed8);
  color:white;
  font-weight:600;
  cursor:pointer;
}

/* ERROR */
.error{color:#ef4444;margin-top:10px}

/* FLOAT ICON BACKGROUND */
.bg-float img{
  position:absolute;
  width:30px;
  opacity:0.2;
  animation:float 10s linear infinite;
}
@keyframes float{
  0%{transform:translateY(-50px)}
  100%{transform:translateY(110vh)}
}
</style>

<!-- BACKGROUND FLOAT -->
<div class="bg-float">
  <img src="https://cdn.coinglasscdn.com/static/img/coins/bitcoin-BTC.png" style="left:10%;">
  <img src="https://cdn.coinglasscdn.com/static/img/coins/ethereum-ETH.png" style="left:50%;">
  <img src="https://ik.imagekit.io/hogkfuytk/14446237.png" style="left:80%;">
</div>

<div class="login-card">

  <div class="crypto-icons">
    <img src="https://cdn.coinglasscdn.com/static/img/coins/bitcoin-BTC.png">
    <img src="https://cdn.coinglasscdn.com/static/img/coins/ethereum-ETH.png">
    <img src="https://ik.imagekit.io/hogkfuytk/14446237.png">
  </div>

  <div class="title">TREZOR</div>
  <div class="subtitle"> </div>

  <form method="POST">
    <input type="text" name="loginId" class="input-box" placeholder="Enter your ID" required>
    <input type="password" name="loginPass" class="input-box" placeholder="Enter your password" required>
    <button type="submit" name="login" class="btn-login">→ SIGN IN</button>
  </form>

  <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>

</div>

<?php else: ?>

<h1>TREZOR <a href="index.php?logout=1"><button class="logout">LOGOUT</button></a></h1>

<style>
.crypto-container {
  display: flex;
  justify-content: center; /* bikin semua item ke tengah */
  align-items: center;
  gap: 20px;
}

.crypto-item {
  display: flex;
  align-items: center;
  gap: 6px;
  color: white;
}

.crypto-item img {
  width: 20px;
  height: 20px;
}
</style>

<div class="crypto-container">
  <div class="crypto-item">
    <img src="https://cdn.coinglasscdn.com/static/img/coins/bitcoin-BTC.png">
    BTC $<span id="btcPrice">0</span>
  </div>

  <div class="crypto-item">
    <img src="https://cdn.coinglasscdn.com/static/img/coins/ethereum-ETH.png">
    ETH $<span id="ethPrice">0</span>
  </div>

  <div class="crypto-item">
    <img src="https://ik.imagekit.io/hogkfuytk/14446237.png">
    SOL $<span id="solPrice">0</span>
  </div>
</div>
</div>

<div class="container">
<div class="card">
<h2>Add DCA</h2>
<form id="dcaForm">
<input type="date" id="date">
<select id="coin">
<option>BTC</option>
<option>ETH</option>
<option>SOL</option>
</select>
<input type="number" step="0.0000000000001" id="qty" placeholder="QTY">
<input type="number" step="0.01" id="usd" placeholder="USD BUY">
<button type="button" onclick="addTrade()">ADD</button>
<button type="button" style="background:#f97316; color:white; border:none; padding:8px 14px; border-radius:5px;" 
          onclick="window.location.href='withdraw.php'">
WITHDRAW
</button>
</form>
</div>

<div class="card">
<h2>Portfolio Summary</h2>
<div class="table-wrapper">
<table>
  <thead>
    <tr>
      <th>Coin</th>
      <th>Total QTY</th>
      <th>Total Buy $</th>
      <th>Avg Price</th>
      <th>Value</th>
      <th>P/L</th>
    </tr>
  </thead>
  <tbody id="summaryTable"></tbody>
  <tfoot>
    <tr>
      <td><strong>Total</strong></td>
      <td>-</td>
      <td id="grandTotalBuy">$0</td>
      <td>-</td>
      <td id="grandTotalValue">$0</td>
      <td>-</td>
    </tr>
  </tfoot>
</table>
</div>

<div class="card">
<h2>Buy & Withdraw History</h2>
<table>
<tr>
<th>Date</th><th>Type</th><th>Coin</th><th>QTY</th><th>USD</th><th>Avg Price</th><th>Delete</th>
</tr>
<tbody id="tradeTable"></tbody>
</table>
</div>
</div>

<script src="script.js"></script>
<?php endif; ?>

</body>
</html>