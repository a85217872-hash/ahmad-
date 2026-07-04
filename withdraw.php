<?php
session_start();

if(!isset($_SESSION['login'])){
header("Location:index.php");
exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Withdraw</title>

<style>

body{
background:#0f172a;
color:white;
font-family:Arial;
text-align:center;
}

.card{
background:#1e293b;
padding:20px;
margin:50px auto;
border-radius:10px;
width:400px;
}

input,select{
padding:8px;
margin:6px;
}

button{
padding:8px 14px;
cursor:pointer;
background:#ef4444;
border:none;
color:white;
border-radius:5px;
}

</style>

</head>

<body>

<div class="card">

<h2>Withdraw</h2>

<input type="date" id="date">

<select id="coin">
<option>BTC</option>
<option>ETH</option>
<option>SOL</option>
</select>

<input type="number" step="0.0000000000001" id="qty" placeholder="QTY Withdraw">
<input type="number" step="0.01" id="usd" placeholder="USD Withdraw">


<button onclick="addWithdraw()">ADD WITHDRAW</button>

<br><br>

<a href="index.php">
<button style="background:#2563eb">BACK</button>
</a>

</div>

<script src="script.js"></script>
<script>
function addWithdraw(){
    let date = document.getElementById("date").value,
        coin = document.getElementById("coin").value,
        qty = parseFloat(document.getElementById("qty").value),
        usd = parseFloat(document.getElementById("usd").value);

    if(!qty || !usd){ alert("Isi QTY dan USD"); return; }

    fetch('add_trade.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({date, coin, qty, usd, type:"withdraw"})
    })
    .then(r=>r.json())
    .then(d=>{
        if(d.status==='ok'){
            alert("Withdraw saved!");
            window.location="index.php";
        } else {
            alert("Error saving withdraw");
        }
    })
    .catch(e=>console.log(e));
}
</script>
</body>
</html>