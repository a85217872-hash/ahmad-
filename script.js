function formatUSD(num){
    return "$" + num.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

let trades = [];
let prices = {BTC:0, ETH:0, SOL:0};

// --- Load trades dari server ---
async function loadTrades(){
    try {
        let res = await fetch('get_trades.php');
        if(!res.ok) throw new Error(res.statusText);
        trades = await res.json();
        render(); // render trades & summary (updateGrandTotals sudah dipanggil di renderSummary)
    } catch(e){
        console.log("Error loading trades:", e);
    }
}

// --- Simpan trade baru ke server ---
async function saveTradeServer(trade){
    try {
        let res = await fetch('add_trade.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(trade)
        });
        let data = await res.json();
        console.log('saveTrade response:', data);
        await loadTrades(); // reload trades setelah simpan
    } catch(e){
        console.log('Error saving trade:', e);
    }
}

// --- Hapus trade dari server ---
async function deleteTradeServer(i){
    try {
        let res = await fetch('delete_trade.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({index: i})
        });
        let data = await res.json();
        console.log('deleteTrade response:', data);
        await loadTrades();
    } catch(e){
        console.log('Error deleting trade:', e);
    }
}

// --- Add BUY ---
function addTrade(){
    let date = document.getElementById("date").value,
        coin = document.getElementById("coin").value,
        qty = parseFloat(document.getElementById("qty").value),
        usd = parseFloat(document.getElementById("usd").value);
    if(!qty || !usd) return;
    let trade = {date, coin, qty, usd, type:"BUY"};
    saveTradeServer(trade);
    document.getElementById("dcaForm").reset();
    document.getElementById("qty").focus();
}

// --- Add WITHDRAW ---
function addWithdraw(){
    let date = document.getElementById("date").value,
        coin = document.getElementById("coin").value,
        qty = parseFloat(document.getElementById("qty").value),
        usd = parseFloat(document.getElementById("usd").value);

    if(!qty || !usd){
        alert("Isi QTY dan USD!");
        return;
    }

    // validasi: tidak boleh withdraw lebih besar dari qty / usd di portfolio
    let coins = {};
    trades.forEach(t=>{
        if(!coins[t.coin]) coins[t.coin]={qty:0, usd:0};
        if(t.type==="WITHDRAW"){ 
            coins[t.coin].qty -= t.qty; 
            coins[t.coin].usd -= t.usd; 
        } else { 
            coins[t.coin].qty += t.qty; 
            coins[t.coin].usd += t.usd; 
        }
    });

    if(coins[coin] && (qty > coins[coin].qty || usd > coins[coin].usd)){
        alert("Withdraw melebihi saldo!");
        return;
    }

    let trade = {date, coin, qty, usd, type:"WITHDRAW"};
    saveTradeServer(trade);
    alert("Withdraw saved!");
    window.location="index.php";
}

// --- Delete ---
function deleteTrade(i){ deleteTradeServer(i); }

// --- Render Trade History ---
function renderTrades(){
    let html = "";
    trades.forEach((t,i)=>{
        let avg = t.usd && t.qty ? t.usd / t.qty : 0;
        let typeText = t.type.toUpperCase();
        let typeColor = t.type.toUpperCase() === "WITHDRAW" ? "#ef4444" : "#22c55e";
        html += `<tr>
            <td>${t.date}</td>
            <td style="color:${typeColor}">${typeText}</td>
            <td>${t.coin}</td>
            <td>${t.qty}</td>
            <td>${formatUSD(t.usd)}</td>
            <td>${formatUSD(avg)}</td>
            <td><button onclick="deleteTrade(${i})">X</button></td>
        </tr>`;
    });
    document.getElementById("tradeTable").innerHTML = html;
}

// --- Update Grand Totals ---
function updateGrandTotals() {
    if(!window.coins) window.coins = {}; // safety check
    let totalBuy = 0;
    let totalValue = 0;

    for(let coin in window.coins){
        let qty = window.coins[coin].qty;
        let invested = window.coins[coin].usd;
        if(invested < 0) invested = 0;
        totalBuy += invested;

        let price = prices[coin] || 0;
        totalValue += qty * price;
    }

    document.getElementById('grandTotalBuy').innerText = formatUSD(totalBuy);
    document.getElementById('grandTotalValue').innerText = formatUSD(totalValue);
}

// --- Render Portfolio Summary ---
function renderSummary(){
    let coins = {};
    let buyOnly = {};

    trades.forEach(t=>{
        let type = t.type.toUpperCase();
        if(!coins[t.coin]) coins[t.coin] = {qty:0, usd:0};
        if(!buyOnly[t.coin]) buyOnly[t.coin] = {qty:0, usd:0};

        if(type === "BUY"){
            coins[t.coin].qty += t.qty;
            coins[t.coin].usd += t.usd;
            buyOnly[t.coin].qty += t.qty;
            buyOnly[t.coin].usd += t.usd;
        } else if(type === "WITHDRAW"){
            let avg = buyOnly[t.coin].qty > 0 ? buyOnly[t.coin].usd / buyOnly[t.coin].qty : 0;
            let usdToDeduct = t.qty * avg;

            coins[t.coin].qty = Math.max(0, coins[t.coin].qty - t.qty);
            coins[t.coin].usd = Math.max(0, coins[t.coin].usd - usdToDeduct);

            buyOnly[t.coin].qty = Math.max(0, buyOnly[t.coin].qty - t.qty);
            buyOnly[t.coin].usd = Math.max(0, buyOnly[t.coin].usd - usdToDeduct);
        }
    });

    let html = "";
    for(let coin in coins){
        let qty = coins[coin].qty;
        if(qty <=0) continue;
        let avg = buyOnly[coin].qty > 0 ? buyOnly[coin].usd / buyOnly[coin].qty : 0;
        let value = qty * (prices[coin] || 0);
        let invested = coins[coin].usd;
        let pl = value - invested;
        let plPercent = invested > 0 ? (pl / invested) * 100 : 0;
        let cls = pl >= 0 ? "profit" : "loss";

        html += `<tr>
<td>${coin}</td>
<td>${qty.toFixed(8)}</td>
<td>${formatUSD(invested)}</td>
<td>${formatUSD(avg)}</td>
<td>${formatUSD(value)}</td>
<td class="${cls}">${formatUSD(pl)} (${plPercent.toFixed(2)}%)</td>
</tr>`;
    }

    document.getElementById("summaryTable").innerHTML = html;
    window.coins = coins;
    updateGrandTotals(); // pastikan grand totals selalu diupdate setelah summary
}

// --- Render full dashboard ---
function render(){ 
    renderTrades(); 
    renderSummary(); 
}

// --- Update real-time BTC & ETH & SOL prices ---
async function updatePrices(){
    try{
        let res = await fetch("https://api.coingecko.com/api/v3/simple/price?ids=bitcoin,ethereum,solana&vs_currencies=usd",{cache:'no-store'});
        let data = await res.json();
        prices.BTC = data.bitcoin.usd;
        prices.ETH = data.ethereum.usd;
        prices.SOL = data.solana.usd;
        document.getElementById("btcPrice").innerText = prices.BTC.toLocaleString();
        document.getElementById("ethPrice").innerText = prices.ETH.toLocaleString();
        document.getElementById("solPrice").innerText = prices.SOL.toLocaleString();
        renderSummary();  // refresh summary setiap update harga
    }catch(e){ console.log("Error fetching prices",e);}
}

setInterval(updatePrices,2000);
updatePrices();
loadTrades();

// === Auto logout jika idle 5 menit ===
let idleTime = 0;
function resetIdleTimer(){idleTime=0;}
['mousemove','keypress','click','scroll','touchstart'].forEach(evt=>{document.addEventListener(evt,resetIdleTimer,false);});
setInterval(()=>{
    idleTime +=30;
    if(idleTime >=300){
        alert("Session expired due to inactivity.");
        window.location.href = "index.php?logout=1";
    }
},30000);