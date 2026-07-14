<?php
require_once __DIR__ . '/../data/class.php';
$admin = new AdminClass();

// --- EXCEL & SAYFA YÖNETİMİ ---
$excel_id = (int)($_GET['excel_id'] ?? 0);
$yeni_mi = isset($_GET['yeni_excel']);
$gecici_ad = $_GET['ad'] ?? 'Yeni Excel';

if ($excel_id <= 0 && !$yeni_mi) { die("Excel bulunamadı"); }

$sayfalar = [];
$aktif_sayfa_id = 0;
$aktif_sayfa_adi = "Sayfa1";

if ($excel_id > 0) {
    $sayfalar = $admin->getSayfalar($excel_id);
    if (!$sayfalar) {
        $admin->sayfaOlustur($excel_id, 'Sayfa1');
        $sayfalar = $admin->getSayfalar($excel_id);
    }
    $aktif_sayfa_id = (int)($_GET['sayfa'] ?? ($sayfalar[0]['sayfa_id'] ?? 0));
    foreach ($sayfalar as $s) {
        if ($s['sayfa_id'] == $aktif_sayfa_id) { $aktif_sayfa_adi = $s['sayfa_adi']; break; }
    }
}

// --- AJAX ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksiyon'])) {
    if (ob_get_length()) ob_clean(); 
    header('Content-Type: application/json');
    if ($_POST['aksiyon'] === 'excel_kaydet_yeni') {
        $yeni_id = $admin->excelOlustur($_POST['excel_adi']);
        if ($yeni_id) {
            $yeni_sayfalar = $admin->getSayfalar($yeni_id);
            $s_id = $yeni_sayfalar[0]['sayfa_id'];
            $admin->saveExcelVerileri($s_id, $_POST['payload']);
            echo json_encode(["status" => "OK", "new_id" => $yeni_id]);
        } else { echo json_encode(["status" => "HATA"]); }
        exit;
    }
    if ($_POST['aksiyon'] === 'excel_kaydet') {
        $sonuc = $admin->saveExcelVerileri((int)$_POST['sayfa_id'], $_POST['payload']);
        echo json_encode(["status" => $sonuc ? "OK" : "HATA"]);
        exit;
    }
    if ($_POST['aksiyon'] === 'yeni_sayfa') {
        $admin->sayfaOlustur((int)$_POST['excel_id'], $_POST['sayfa_adi']);
        echo json_encode(["status" => "OK"]);
        exit;
    }
    if ($_POST['aksiyon'] === 'sayfa_isimlendir') {
        $admin->sayfaGuncelle((int)$_POST['sayfa_id'], $_POST['yeni_ad']);
        echo json_encode(["status" => "OK"]);
        exit;
    }
}

$veriler = [];
if ($aktif_sayfa_id > 0) {
    $ham = $admin->getExcelVerileri($aktif_sayfa_id);
    foreach ($ham as $h) { $veriler[$h['hucre_konumu']] = $h['icerik']; }
}

$maxSatir = ($aktif_sayfa_id > 0) ? $admin->getMaxSatir($aktif_sayfa_id) : 0;
$toplamSatir = max(50, $maxSatir);
$maxSutunDb = ($aktif_sayfa_id > 0) ? $admin->getMaxSutun($aktif_sayfa_id) : 0;
$toplamSutun = max(8, $maxSutunDb);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($aktif_sayfa_adi) ?> - Erportal Excel</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    
    <style>
        :root { --excel-green: #217346; --border: #dcdcdc; --neon-red: #e74c3c; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; background: #f0f0f0; overflow-x: hidden; }
        
        /* Toolbar */
        .toolbar { position: sticky; top: 0; z-index: 1000; background: var(--excel-green); color: white; padding: 8px 15px; display: flex; gap: 10px; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.2); }
        .btn-t { background: white; border: none; padding: 6px 15px; border-radius: 4px; cursor: pointer; color: var(--excel-green); font-weight: bold; font-size: 12px; display: flex; align-items: center; gap: 8px; transition: 0.2s; }
        .btn-t:active { transform: scale(0.95); }* PDF Butonu (Güncellendi: Beyaz Arka Plan) */
        .btn-pdf { background: white !important; color: var(--excel-green) !important; border: 1px solid white; transition: 0.3s; }
        .btn-pdf:hover { background: #f8f9fa !important; box-shadow: 0 0 15px rgba(255,255,255,0.4); }
        .save-tick { display: none; color: #2ecc71; font-size: 16px; animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        @keyframes popIn { from { transform: scale(0); opacity: 0; } to { transform: scale(1.2); opacity: 1; } }

        /* Grid */
        .grid-container { background: #fff; height: calc(100vh - 80px); overflow: auto; position: relative; width: 100%; transition: 0.4s; }
        table { border-collapse: collapse; table-layout: fixed; width: max-content; cursor: cell; }
        th, td { border: 1px solid var(--border); padding: 5px; font-size: 13px; min-width: 100px; height: 22px; position: relative; outline: none; }
        th { background: #f8f9fa; color: #666; position: sticky; top: 0; z-index: 20; text-align: center; }
        .row-idx { width: 40px; left: 0; z-index: 21; position: sticky; background: #f8f9fa; font-weight: bold; }
        td:focus { border: 2px solid var(--excel-green) !important; z-index: 10; background: #fff !important; }
        .selected-range { background-color: rgba(33, 115, 70, 0.2) !important; outline: 1px solid var(--excel-green) !important; }

        /* Fill Handle */
        #fill-handle { position: absolute; width: 8px; height: 8px; background: var(--excel-green); border: 1px solid white; cursor: crosshair; display: none; z-index: 1000; box-shadow: 0 0 5px rgba(0,0,0,0.2); }

        /* PDF Modu UI */
        #pdf-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0); z-index: 9998; display: none; pointer-events: none; transition: 0.6s; }
        #pdf-overlay.active { background: rgba(0,0,0,0.85); pointer-events: auto; }
        #pdf-overlay::after { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.1) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.03), rgba(0, 255, 0, 0.01), rgba(0, 0, 255, 0.03)); z-index: 9999; background-size: 100% 2px, 3px 100%; pointer-events: none; }
        #pdf-confirm-btn { position: fixed; bottom: 40px; left: 50%; transform: translateX(-50%) translateY(100px); z-index: 10001; opacity: 0; padding: 15px 40px; background: var(--excel-green); color: #fff; border: 2px solid #fff; border-radius: 50px; cursor: pointer; transition: 0.5s; font-weight: bold; }
        #pdf-confirm-btn.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .pdf-mode-active { position: relative !important; z-index: 10000 !important; box-shadow: 0 0 50px rgba(255,255,255,0.2); background: white !important; }

        /* PDF Preview Modal (Fütüristik Ön İzleme) */
        #pdf-preview-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(10px); z-index: 20000; display: none; align-items: center; justify-content: center; }
        .pdf-preview-card { background: #fff; width: 80%; height: 85%; border-radius: 12px; display: flex; flex-direction: column; box-shadow: 0 25px 50px rgba(0,0,0,0.5); overflow: hidden; border: 1px solid rgba(255,255,255,0.2); }
        .pdf-preview-header { padding: 15px 25px; background: var(--excel-green); color: white; display: flex; justify-content: space-between; align-items: center; }
        .pdf-preview-body { flex-grow: 1; background: #525659; position: relative; }
        .pdf-preview-body iframe { width: 100%; height: 100%; border: none; }
        .pdf-preview-footer { padding: 15px; display: flex; justify-content: flex-end; gap: 10px; background: #f8f9fa; }

        /* Diğer UI */
        #er-toast { position: fixed; top: 20px; right: 20px; padding: 15px 25px; background: #333; color: #fff; border-radius: 5px; z-index: 11000; transform: translateX(200%); transition: 0.5s; box-shadow: 0 5px 15px rgba(0,0,0,0.3); border-left: 5px solid var(--excel-green); }
        #er-toast.show { transform: translateX(0); }
        #er-prompt-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 12000; display: none; align-items: center; justify-content: center; backdrop-filter: blur(3px); }
        .er-prompt-card { background: white; padding: 25px; border-radius: 8px; width: 350px; border-top: 5px solid var(--excel-green); }
        .er-prompt-card input { width: 100%; padding: 10px; margin: 15px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }

        .sheet-bar { background: #e0e0e0; display: flex; padding: 0 5px; border-top: 1px solid #ccc; height: 40px; position: fixed; bottom: 0; left: 0; width: 100%; z-index: 999; align-items: flex-end; }
        .tab { padding: 8px 20px; background: #ccc; margin-right: 2px; cursor: pointer; font-size: 12px; border-radius: 5px 5px 0 0; transition: 0.2s; }
        .tab.active { background: #fff; font-weight: bold; color: var(--excel-green); border-top: 3px solid var(--excel-green); }
    </style>
</head>
<body>

<div id="pdf-preview-overlay">
    <div class="pdf-preview-card">
        <div class="pdf-preview-header">
            <h3 style="margin:0">📄 PDF Ön İzleme</h3>
            <button class="btn-t" onclick="closePdfPreview()" style="background:rgba(255,255,255,0.2); color:white; border:1px solid white;">✖</button>
        </div>
        <div class="pdf-preview-body">
            <iframe id="pdf-frame"></iframe>
        </div>
        <div class="pdf-preview-footer">
            <button class="btn-t" onclick="closePdfPreview()" style="background:#eee">Vazgeç</button>
            <button class="btn-t" id="final-download-btn" style="background:var(--excel-green); color:white">💾 MÜHRÜ BAS (İNDİR)</button>
        </div>
    </div>
</div>

<div id="er-toast">Mesaj</div>
<div id="er-prompt-overlay">
    <div class="er-prompt-card">
        <h3 id="prompt-title" style="margin:0">Girdi</h3>
        <input type="text" id="prompt-input">
        <div style="text-align:right">
            <button class="btn-t" onclick="closePrompt()" style="background:#eee">İptal</button>
            <button class="btn-t" id="prompt-confirm" style="background:var(--excel-green); color:white">Tamam</button>
        </div>
    </div>
</div>

<div id="pdf-overlay"></div>
<button id="pdf-confirm-btn" onclick="seciliAlaniPdfYap()">⚡ ÖN İZLEMEYİ GÖSTER</button>

<div class="toolbar">
    <div class="page-title" onclick="customPrompt('Sayfa ismini değiştirin:', '<?= addslashes($aktif_sayfa_adi) ?>', sayfaIsimlendirAction)"><?= htmlspecialchars($aktif_sayfa_adi) ?></div>
    <div style="flex-grow: 1;"></div>
    
    <button class="btn-t" id="btn-save" onclick="verileriKaydet()">
        <span id="save-text">💾 KAYDET</span>
        <span class="save-tick" id="save-tick">✅</span>
    </button>

    <button class="btn-t btn-pdf" onclick="pdfModunuAc()">📄 PDF AL</button>
    <button class="btn-t" onclick="satirEkle()">➕ Satır</button>
    <button class="btn-t" onclick="sutunEkle()">➕ Sütun</button>
</div>

<div class="grid-container" id="grid-main">
    <table id="excelTable">
        <thead>
            <tr id="headerRow" class="drag-row">
                <th class="row-idx">#</th>
                <?php
                    $cols = [];
                    for ($i = 0; $i < $toplamSutun; $i++) { $cols[] = chr(65 + $i); }
                    foreach ($cols as $idx => $c):
                ?>
                <th class="col-header" draggable="true" ondragstart="colDrag(event, <?= $idx+1 ?>)" ondrop="colDrop(event, <?= $idx+1 ?>)" ondragover="allowDrop(event)"><?= $c ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php for ($i = 1; $i <= $toplamSatir; $i++): ?>
            <tr class="drag-row" draggable="true" ondragstart="rowDrag(event, <?= $i ?>)" ondrop="rowDrop(event, <?= $i ?>)" ondragover="allowDrop(event)">
                <th class="row-idx"><?= $i ?></th>
                <?php foreach($cols as $c): 
                    $konum = $c . $i;
                    $val = $veriler[$konum] ?? '';
                ?>
                <td contenteditable="true" data-konum="<?= $konum ?>"><?= htmlspecialchars($val) ?></td>
                <?php endforeach; ?>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>
</div>

<div class="sheet-bar">
    <?php if($excel_id > 0): ?>
        <?php foreach($sayfalar as $s): ?>
            <div class="tab <?= $s['sayfa_id'] == $aktif_sayfa_id ? 'active' : '' ?>" onclick="location.href='?excel_id=<?= $excel_id ?>&sayfa=<?= $s['sayfa_id'] ?>'"><?= htmlspecialchars($s['sayfa_adi']) ?></div>
        <?php endforeach; ?>
        <div class="tab" onclick="customPrompt('Yeni Sayfa Adı:', '', yeniSayfaAction)">➕ Yeni Sayfa</div>
    <?php else: ?>
        <div class="tab active"><?= htmlspecialchars($aktif_sayfa_adi) ?></div>
    <?php endif; ?>
</div>

<script>
// --- GLOBAL DEĞİŞKENLER ---
let isSelecting = false, isPdfMode = false, startCell = null, handleSelecting = false;
let sourceIdx = null, sourceRowIdx = null, undoStack = [];
const maxUndoSteps = 50, table = document.getElementById('excelTable');
const fillHandle = document.createElement('div');
fillHandle.id = 'fill-handle'; document.body.appendChild(fillHandle);

// --- UI HELPERS ---
function showToast(msg) {
    const t = document.getElementById('er-toast'); t.innerText = msg; t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3000);
}
function customPrompt(title, val, action) {
    const o = document.getElementById('er-prompt-overlay'), i = document.getElementById('prompt-input');
    document.getElementById('prompt-title').innerText = title; i.value = val; o.style.display = 'flex'; i.focus();
    document.getElementById('prompt-confirm').onclick = () => { action(i.value); closePrompt(); };
}
function closePrompt() { document.getElementById('er-prompt-overlay').style.display = 'none'; }

// --- SELECTION ---
function clearSelections() { document.querySelectorAll('.selected-range').forEach(el => el.classList.remove('selected-range')); }

function updateHandle(target) {
    if (!target || target.tagName !== 'TD' || isPdfMode) { fillHandle.style.display = 'none'; return; }
    const rect = target.getBoundingClientRect();
    fillHandle.style.display = 'block'; fillHandle.style.top = (rect.bottom + window.scrollY - 4) + 'px'; fillHandle.style.left = (rect.right + window.scrollX - 4) + 'px';
}

function selectRange(cell1, cell2) {
    if (!cell1 || !cell2) return;
    const r1 = cell1.parentElement.rowIndex, r2 = cell2.parentElement.rowIndex, c1 = cell1.cellIndex, c2 = cell2.cellIndex;
    const minR = Math.min(r1, r2), maxR = Math.max(r1, r2), minC = Math.min(c1, c2), maxC = Math.max(c1, c2);
    clearSelections();
    for (let r = minR; r <= maxR; r++) { for (let c = minC; c <= maxC; c++) { table.rows[r].cells[c].classList.add('selected-range'); } }
}

// --- UNDO ---
function saveStateForUndo() {
    let currentState = { focus: document.activeElement.dataset?.konum || null, data: [] };
    document.querySelectorAll('[data-konum]').forEach(td => { if (td.innerText.trim() !== "") currentState.data.push({ k: td.dataset.konum, v: td.innerText }); });
    undoStack.push(currentState); if (undoStack.length > maxUndoSteps) undoStack.shift();
}
function undo() {
    if (undoStack.length === 0) return; let prev = undoStack.pop();
    document.querySelectorAll('[data-konum]').forEach(td => td.innerText = ""); 
    prev.data.forEach(item => { let c = document.querySelector(`[data-konum="${item.k}"]`); if (c) c.innerText = item.v; });
    if (prev.focus) { let tc = document.querySelector(`[data-konum="${prev.focus}"]`); if (tc) tc.focus(); }
    //verileriKaydet(true);
}

// --- DATA ---
function verileriKaydet(isAuto = false) {
    if (!isAuto) { document.getElementById('save-text').style.display = 'none'; document.getElementById('save-tick').style.display = 'inline-block'; }
    let payload = [];
    document.querySelectorAll('[data-konum]').forEach(td => { if (td.innerText.trim() !== '') payload.push({ hucre: td.dataset.konum, deger: td.innerText.trim() }); });
    let fd = new FormData(); let isN = <?= $excel_id > 0 ? 'false' : 'true' ?>;
    if (isN) { fd.append('aksiyon', 'excel_kaydet_yeni'); fd.append('excel_adi', '<?= addslashes($gecici_ad) ?>'); }
    else { fd.append('aksiyon', 'excel_kaydet'); fd.append('sayfa_id', '<?= $aktif_sayfa_id ?>'); }
    fd.append('payload', JSON.stringify(payload));
    fetch(window.location.href, { method: 'POST', body: fd }).then(res => res.json()).then(res => {
        if (!isAuto) { setTimeout(() => { document.getElementById('save-text').style.display = 'inline-block'; document.getElementById('save-tick').style.display = 'none'; }, 2000); }
        if (res.status === "OK" && isN) window.location.href = "excel.php?excel_id=" + res.new_id;
    });
}

// --- KLAVYE NAVİGASYONU VE GERİ AL (CTRL+Z) ---
document.addEventListener('keydown', e => {
    // CTRL+Z (Geri Al)
    if (e.ctrlKey && e.key.toLowerCase() === 'z') { 
        e.preventDefault(); 
        undo(); 
        return; 
    }

    // ESC (PDF ve Modal Çıkışları)
    if (e.key === 'Escape' && isPdfMode) { pdfModunuKapat(); return; }
    if (e.key === 'Escape' && document.getElementById('pdf-preview-overlay').style.display === 'flex') { closePdfPreview(); return; }
    
    // Yön Tuşları ve Enter Navigasyonu
    const a = document.activeElement; 
    if (a.tagName !== 'TD') return;
    
    let r = a.parentElement.rowIndex, c = a.cellIndex, isAr = ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key);
    if (isAr || e.key === 'Enter') { if (!e.ctrlKey) { clearSelections(); startCell = null; } else if (isAr && !startCell) { startCell = a; } }
    let t = null;
    switch (e.key) {
        case 'ArrowUp': if (r > 1) t = table.rows[r - 1].cells[c]; break;
        case 'ArrowDown': if (r < table.rows.length - 1) t = table.rows[r + 1].cells[c]; break;
        case 'ArrowLeft': if (c > 1) t = a.previousElementSibling; break;
        case 'ArrowRight': if (c < a.parentElement.cells.length - 1) t = a.nextElementSibling; break;
        case 'Enter': e.preventDefault(); saveStateForUndo(); if (r < table.rows.length - 1) table.rows[r + 1].cells[c].focus(); return;
    }
    if (t) { t.focus(); if (e.ctrlKey && isAr) selectRange(startCell, t); e.preventDefault(); }
});

// --- KESİN ÇALIŞAN KOPYALAMA (COPY EVENT) ---
document.addEventListener('copy', e => {
    const selected = document.querySelectorAll('.selected-range');
    // Eğer seçili alan yoksa ve sadece 1 hücredeysek onu da kopyalamaya dahil edelim
    const targetCells = selected.length > 0 ? selected : (document.activeElement.tagName === 'TD' ? [document.activeElement] : []);
    
    if (targetCells.length > 0) {
        e.preventDefault(); // Tarayıcının standart kopyalamasını durdur
        let matrix = {};
        targetCells.forEach(td => {
            let r = td.parentElement.rowIndex, c = td.cellIndex;
            if (!matrix[r]) matrix[r] = [];
            matrix[r][c] = td.innerText;
        });

        let clipboardData = "";
        Object.keys(matrix).sort((a,b)=>a-b).forEach(r => {
            let rowArr = [];
            Object.keys(matrix[r]).sort((a,b)=>a-b).forEach(c => {
                rowArr.push(matrix[r][c] || "");
            });
            clipboardData += rowArr.join("\t") + "\n";
        });

        // HTTP/HTTPS takılmadan panoya yazma yöntemi:
        if (e.clipboardData) {
            e.clipboardData.setData('text/plain', clipboardData.trimEnd());
            showToast("Kopyalandı!");
        }
    }
});

// --- KESİN ÇALIŞAN YAPIŞTIRMA (PASTE EVENT) ---
document.addEventListener('paste', e => {
    const activeCell = document.activeElement;
    if (activeCell.tagName !== 'TD') return;
    
    e.preventDefault(); // Tarayıcının hücre içine html yapıştırmasını durdur
    try {
        // İzin istemeden direkt olayın (event) içinden panoyu okuyoruz
        const text = (e.clipboardData || window.clipboardData).getData('text');
        if (!text) return;
        
        saveStateForUndo();
        const rows = text.split('\n');
        let startRow = activeCell.parentElement.rowIndex;
        let startCol = activeCell.cellIndex;

        rows.forEach((row, rIdx) => {
            // Tamamen boş satırları atla
            if (!row.trim() && rows.length > 1) return; 
            
            const cols = row.split('\t');
            cols.forEach((val, cIdx) => {
                let targetRow = table.rows[startRow + rIdx];
                if (targetRow) {
                    let targetCell = targetRow.cells[startCol + cIdx];
                    if (targetCell) targetCell.innerText = val.trim();
                }
            });
        });
        //verileriKaydet(true);
        showToast("Yapıştırıldı!");
    } catch (err) { 
        console.error("Yapıştırma hatası:", err); 
        showToast("Yapıştırma başarısız!");
    }
});

fillHandle.addEventListener('mousedown', e => { handleSelecting = true; startCell = document.activeElement; e.preventDefault(); });

table.addEventListener('mousedown', e => {
    if (e.target.tagName !== 'TD') return;
    if (isPdfMode) { isSelecting = true; startCell = e.target; if (!e.ctrlKey) clearSelections(); e.target.classList.add('selected-range'); } 
    else { clearSelections(); startCell = e.target; if (e.ctrlKey) isSelecting = true; }
});

table.addEventListener('mouseover', e => {
    if (e.target.tagName !== 'TD') return;
    if (isSelecting || handleSelecting) selectRange(startCell, e.target);
});

window.addEventListener('mouseup', () => { isSelecting = false; handleSelecting = false; });

// --- PDF MODU & PREVIEW ---
function pdfModunuAc() {
    isPdfMode = true; document.querySelectorAll('.drag-row, .col-header').forEach(el => el.setAttribute('draggable', 'false'));
    const ov = document.getElementById('pdf-overlay'); ov.style.display = 'block'; setTimeout(() => ov.classList.add('active'), 10);
    document.getElementById('pdf-confirm-btn').classList.add('show'); document.getElementById('grid-main').classList.add('pdf-mode-active');
    clearSelections(); showToast("PDF: Mouse ile istediğiniz alanı sürükleyerek boyayın. ESC ile çıkış.");
}
function pdfModunuKapat() {
    isPdfMode = false; document.querySelectorAll('.drag-row, .col-header').forEach(el => el.setAttribute('draggable', 'true'));
    const ov = document.getElementById('pdf-overlay'); ov.classList.remove('active'); setTimeout(() => ov.style.display = 'none', 600);
    document.getElementById('pdf-confirm-btn').classList.remove('show'); document.getElementById('grid-main').classList.remove('pdf-mode-active'); clearSelections();
}

async function seciliAlaniPdfYap() {
    const { jsPDF } = window.jspdf, sc = document.querySelectorAll('.selected-range');
    if (sc.length === 0) { showToast("Önce bir alan seçmelisiniz!"); return; }
    let rd = {}; sc.forEach(td => { let r = td.parentElement.rowIndex; if (!rd[r]) rd[r] = []; rd[r].push(td.innerText); });
    
    const doc = new jsPDF('p', 'pt', 'a4');
    doc.text("Rapor - <?= addslashes($aktif_sayfa_adi) ?>", 40, 40);
    doc.autoTable({ startY: 60, body: Object.values(rd), theme: 'grid' });
    
    // Ön İzleme Bloğu
    const pdfDataUri = doc.output('datauristring');
    document.getElementById('pdf-frame').src = pdfDataUri;
    document.getElementById('pdf-preview-overlay').style.display = 'flex';
    
    document.getElementById('final-download-btn').onclick = () => {
        doc.save("Excel_Rapor.pdf");
        closePdfPreview();
        pdfModunuKapat();
    };
}

function closePdfPreview() { document.getElementById('pdf-preview-overlay').style.display = 'none'; }

// --- TOOLBAR ---
function sayfaIsimlendirAction(ad) { if (!ad) return; let fd = new FormData(); fd.append('aksiyon', 'sayfa_isimlendir'); fd.append('sayfa_id', '<?= $aktif_sayfa_id ?>'); fd.append('yeni_ad', ad); fetch(window.location.href, { method: 'POST', body: fd }).then(() => location.reload()); }
function yeniSayfaAction(ad) { if (!ad) return; let fd = new FormData(); fd.append('aksiyon', 'yeni_sayfa'); fd.append('excel_id', '<?= $excel_id ?>'); fd.append('sayfa_adi', ad); fetch(window.location.href, { method: 'POST', body: fd }).then(() => location.reload()); }
function satirEkle() { saveStateForUndo(); const tb = document.getElementById('tableBody'), nIdx = tb.rows.length + 1, cC = document.getElementById('headerRow').cells.length; let r = tb.insertRow(); r.draggable = true; r.className = "drag-row"; let th = document.createElement('th'); th.className = 'row-idx'; th.innerText = nIdx; r.appendChild(th); for(let i=1; i < cC; i++) { let c = r.insertCell(); c.contentEditable = "true"; let h = document.getElementById('headerRow').cells[i].innerText.trim(); c.dataset.konum = h + nIdx; } }
function sutunEkle() { saveStateForUndo(); const hr = document.getElementById('headerRow'), cC = hr.cells.length, nCh = String.fromCharCode(64 + cC); let th = document.createElement('th'); th.innerText = nCh; th.draggable = true; th.className = "col-header"; hr.appendChild(th); Array.from(document.getElementById('tableBody').rows).forEach((r, idx) => { let c = r.insertCell(); c.contentEditable = "true"; c.dataset.konum = nCh + (idx + 1); }); }
function allowDrop(e) { e.preventDefault(); }
function colDrag(e, idx) { sourceIdx = idx; }
function colDrop(e, tIdx) { e.preventDefault(); if (sourceIdx === null || tIdx === 0 || sourceIdx === tIdx) return; saveStateForUndo(); for (let r of table.rows) { let s = r.cells[sourceIdx], t = r.cells[tIdx]; if(s && t) { let tmp = s.innerHTML; s.innerHTML = t.innerHTML; t.innerHTML = tmp; } } //verileriKaydet(true); 
}
function rowDrag(e, idx) { sourceRowIdx = idx; }
function rowDrop(e, tRIdx) { e.preventDefault(); if (sourceRowIdx === null || tRIdx === 0 || sourceRowIdx === tRIdx) return; saveStateForUndo(); let sr = table.rows[sourceRowIdx], tr = table.rows[tRIdx]; for(let i = 1; i < sr.cells.length; i++) { let tmp = sr.cells[i].innerHTML; sr.cells[i].innerHTML = tr.cells[i].innerHTML; tr.cells[i].innerHTML = tmp; } //verileriKaydet(true);
 }

document.addEventListener('focusin', e => { if (e.target.tagName === 'TD') updateHandle(e.target); });
window.addEventListener('scroll', () => updateHandle(document.activeElement));
</script>
</body>
</html>