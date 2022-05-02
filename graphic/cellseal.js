//---------------------------------------------
// 定数定義
//---------------------------------------------
// 保存を行うプログラムがあるURL
const SAVE_URL = '../graphic/receive.php';

// 画像が保存されているURL
const IMAGE_URL = '../graphic/image';

//---------------------------------------------
// オブジェクト
//---------------------------------------------
const Banner = {
    bgcolor: "#ffffff",             // 背景色
    basecolor:  "#ffff00",          // ベース色
    font: "bold 50px sans-serif",   // フォント
    fontcolor: "Blue",              // 文字色
    text: "OK",                     // テキスト

    // Base情報
    base: {
        width: null,        // 横幅
        height: null,       // 高さ
        shape: "rectangle"  // 形
    },
    
    // Canvas情報
    canvas: {
        width: null,   // 横幅
        height: null,  // 高さ
        ctx: null      // context
    }
}

const ResBanner = {
    bgcolor: "#ffffff",     // 背景色
    ctx: null,              // context

    // Cell情報
    cell: {
        width: null,   // 横幅
        height: null,  // 高さ
        shape: null,   // 形
    },
}

//---------------------------------------------
// [event] ページ読み込み完了
//---------------------------------------------
window.onload = ()=>{
    const message       = document.querySelector("#txt-message");   // テキストボックス
    const colorText     = document.querySelector("#color-text");    // 文字色
    const sizeText      = document.querySelector("#size-text");     // 文字サイズ
    const shapeBase     = document.querySelector("#shape-base");    // ベース形
    const colorBase     = document.querySelector("#color-base");    // ベース色
    const widthBase     = document.querySelector("#width-base");    // ベース幅
    const heightBase    = document.querySelector("#height-base");   // ベース高

    // Canvasの情報を代入
    const board = document.querySelector("#board");
    Banner.canvas.ctx    = board.getContext("2d");
    Banner.canvas.width  = board.width;   // 横幅
    Banner.canvas.height = board.height;  // 高さ
    // Baseの情報を代入
    Banner.base.width  = widthBase.value;   // 横幅
    Banner.base.height = heightBase.value;  // 高さ
    Banner.base.shape = shapeBase.value;  // 形

    // Canvasの情報を代入
    const sheet = document.querySelector("#sheet");
    ResBanner.ctx    = sheet.getContext("2d");
    Banner.canvas.width  = board.width;   // 横幅
    Banner.canvas.height = board.height;  // 高さ
    // Baseの情報を代入
    Banner.base.width  = widthBase.value;   // 横幅
    Banner.base.height = heightBase.value;  // 高さ
    Banner.base.shape = shapeBase.value;  // 形
    
    drawCanvas();

    //---------------------------------------------
    // ユーザーの入力があればCanvasを更新する
    //---------------------------------------------
    // 文字入力
    message.addEventListener("keyup", ()=>{
        Banner.text = message.value;
        drawCanvas();
    });

    // 文字色の変更
    colorText.addEventListener("change", ()=>{
        Banner.fontcolor = colorText.value;
        drawCanvas();
    });

    // 文字サイズの変更
    sizeText.addEventListener("change", ()=>{
        Banner.font = "bold " + String(sizeText.value) + "px sans-serif";
        drawCanvas();
    });
        
    // // ベース形の変更
    if (document.querySelector("#shape-base")) {
        document.querySelectorAll("#shape-base").forEach((elem) => {
          elem.addEventListener("change", function(event) {
            Banner.base.shape = event.target.value;
            drawCanvas();
          });
        });
    }
        
    // ベース色の変更
    colorBase.addEventListener("change", ()=>{
        Banner.basecolor = colorBase.value;
        drawCanvas();
    })

    // ベース幅の変更
    widthBase.addEventListener("change", ()=>{
        Banner.base.width = widthBase.value;
        drawCanvas();
    })

    // ベース高の変更
    heightBase.addEventListener("change", ()=>{
        Banner.base.height = heightBase.value;
        drawCanvas();
    })

    // submitイベントが発生したらキャンセル
    document.querySelector("#frm").addEventListener("submit", (e)=>{
        e.preventDefault();
    });

    //---------------------------------------------
    // 並べるボタンが押されたら出来上がりを表示する
    //---------------------------------------------
    document.querySelector("#btn-sort").addEventListener("click", ()=>{
    
    });

    //---------------------------------------------
    // 保存ボタンが押されたらサーバへ送信する
    //---------------------------------------------
    document.querySelector("#btn-send").addEventListener("click", ()=>{
        // Canvasのデータを取得
        const canvas = sheet.toDataURL("image/png");  // DataURI Schemaが返却される

        // 送信情報の設定
        const param  = {
        method: "POST",
        headers: {
            "Content-Type": "application/json; charset=utf-8"
        },
        body: JSON.stringify({data: canvas})
        };

        // サーバへ送信
        sendServer(SAVE_URL, param);
    });
};


/**
 * Canvasを描画
 *
 * @return {void}
 */

function drawSheet(){
    ctx    = Banner.canvas.ctx;
    width  = Banner.canvas.width;
    height = Banner.canvas.height;
    baseshape = Banner.base.shape;
    basewidth  = Banner.base.width;
    baseheight = Banner.base.height;

    // Canvasをお掃除
    ctx.clearRect(0, 0, width, height);

    // Baseを描画
    ctx.fillStyle = Banner.basecolor;
    if(baseshape=="rectangle"){
        ctx.fillRect((height-basewidth)/2, (width-baseheight)/2, basewidth, baseheight);
    } else {

        var PI2=Math.PI*2;
        var ratio=(Math.min(baseheight,basewidth)/Math.max(baseheight,basewidth));
        var radius=Math.max(width,height)/2;
        var increment = 1 / radius;
    
        ctx.beginPath();
        var x = width/2 + radius * Math.cos(0);
        var y = height/2 - ratio * radius * Math.sin(0);
        if(baseheight<basewidth){
            ctx.lineTo(x,y);
        } else {
            ctx.lineTo(y,x);
        }
    
        for(var radians=increment; radians<PI2; radians+=increment){ 
            var x = width/2 + radius * Math.cos(radians);
            var y = height/2 - ratio * radius * Math.sin(radians);
            if(baseheight<basewidth){
                ctx.lineTo(x,y);
            } else {
                ctx.lineTo(y,x);
            }
        }    
        ctx.closePath();
        ctx.fill();
    }

    // 文字を描画
    ctx.font = Banner.font;
    ctx.textAlign = "center";     // 文字揃え
    ctx.textBaseline = "middle";  // 文字高さ揃え
    ctx.fillStyle = Banner.fontcolor;
    ctx.fillText(Banner.text, width/2, height/2, width);

    // 枠を付ける
    ctx.strokeStyle = 'rgb(0, 0, 0)';
    ctx.strokeRect(0, 0, width, height);
    ctx.strokeStyle = null;

}

function drawCanvas(){
    ctx    = Banner.canvas.ctx;
    width  = Banner.canvas.width;
    height = Banner.canvas.height;
    baseshape = Banner.base.shape;
    basewidth  = Banner.base.width;
    baseheight = Banner.base.height;

    // Canvasをお掃除
    ctx.clearRect(0, 0, width, height);

    // Baseを描画
    ctx.fillStyle = Banner.basecolor;
    if(baseshape=="rectangle"){
        ctx.fillRect((height-basewidth)/2, (width-baseheight)/2, basewidth, baseheight);
    } else {
        var PI2=Math.PI*2;
        var ratio=(Math.min(basewidth,baseheight)/Math.max(basewidth,baseheight));
        // var radius=Math.max(width,height)/2;
        var radius=Math.max(basewidth,baseheight)/2;
        var increment = 1 / radius;
    
        ctx.beginPath();
        var x = width/2 + radius * Math.cos(0);
        var y = height/2 - ratio * radius * Math.sin(0);
        if(parseFloat(baseheight)<=parseFloat(basewidth)){
            ctx.lineTo(x,y);
        } else {
            ctx.lineTo(y,x);
        }
    
        for(var radians=increment; radians<PI2; radians+=increment){ 
            var x = width/2 + radius * Math.cos(radians);
            var y = height/2 - ratio * radius * Math.sin(radians);
            if(parseFloat(baseheight)<=parseFloat(basewidth)){
                ctx.lineTo(x,y);
            } else {
                ctx.lineTo(y,x);
            }
        }    
        ctx.closePath();
        ctx.fill();
    }

    // 文字を描画
    ctx.font = Banner.font;
    ctx.textAlign = "center";     // 文字揃え
    ctx.textBaseline = "middle";  // 文字高さ揃え
    ctx.fillStyle = Banner.fontcolor;
    ctx.fillText(Banner.text, width/2, height/2, width);

    // 枠を付ける
    ctx.strokeStyle = 'rgb(0, 0, 0)';
    ctx.strokeRect(0, 0, width, height);
    ctx.strokeStyle = null;

}

/**
 * サーバへJSON送信
 *
 * @param url   {string} 送信先URL
 * @param param {object} fetchオプション
 */
function sendServer(url, param){
    fetch(url, param)
        .then((response)=>{
        return response.json();
        })
        .then((json)=>{
        if(json.status){
            alert("送信に『成功』しました");
            setImage(json.result);    //json.resultにはファイル名が入っている
        }
        else{
            alert("送信に『失敗』しました");
            console.log(`[error1] ${json.result}`);
        }
        })
        .catch((error)=>{
        alert("送信に『失敗』しました");
        console.log(`[error2] ${error}`);
        });
}

/**
 * サーバ上の画像を表示する
 *
 * @param path {string} 画像のURL
 * @return void
 */
function setImage(path){
  const url = `${IMAGE_URL}/${path}`;
  const result = document.querySelector("#result");
  const li = document.createElement("li");
  li.innerHTML = `<a href="${url}" target="_blank" rel="noopener noreferrer"><img src="${url}" class="saveimage"></a>`;
  result.insertBefore(li, result.firstChild);
}