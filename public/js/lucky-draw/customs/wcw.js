
const dataDiv = document.getElementById('data-clients');
const time = dataDiv.getAttribute('data-time');
const assigneeId = dataDiv.getAttribute('data-assignee_id');
const participantsData = dataDiv.getAttribute('data-clients');
const participantsJson = JSON.parse(participantsData);
const rewardOrder = parseInt(dataDiv.getAttribute('data-order')) || 0;
const rewardOrderName = dataDiv.getAttribute('data-order_name') || '';
const luckyDrawId = dataDiv.getAttribute('data-lucky_draw_id') || 'default';

// Key để lưu danh sách người bị bỏ qua vào localStorage
const SKIPPED_STORAGE_KEY = `skipped_ids_lucky_draw_${luckyDrawId}`;

// ========== QUẢN LÝ DANH SÁCH NGƯỜI BỊ BỎ QUA (SKIPPED) ==========
// Load danh sách người bị bỏ qua từ localStorage
function loadSkippedIds() {
    try {
        const stored = localStorage.getItem(SKIPPED_STORAGE_KEY);
        return stored ? JSON.parse(stored) : [];
    } catch (e) {
        console.error('Lỗi khi đọc skippedIds từ localStorage:', e);
        return [];
    }
}

// Lưu danh sách người bị bỏ qua vào localStorage
function saveSkippedIds(ids) {
    try {
        localStorage.setItem(SKIPPED_STORAGE_KEY, JSON.stringify(ids));
    } catch (e) {
        console.error('Lỗi khi lưu skippedIds vào localStorage:', e);
    }
}

// Thêm một người vào danh sách bị bỏ qua (so sánh id dạng string để tránh trùng 123 vs "123")
function addToSkippedIds(id, name) {
    const list = loadSkippedIds();
    if (!list.some(sid => String(sid) === String(id))) {
        list.push(id);
        saveSkippedIds(list);
        console.log(`Đã bỏ qua người có ID: ${id}, Tên: ${name}`);
        console.log(`Danh sách bị bỏ qua (${list.length} người): ${list.join(', ')}`);
    }
    return list;
}

// Xóa toàn bộ danh sách bị bỏ qua (reset) - gọi từ Console: clearSkippedIds()
function clearSkippedIds() {
    localStorage.removeItem(SKIPPED_STORAGE_KEY);
    console.log('Đã xóa danh sách người bị bỏ qua. Reload trang để áp dụng.');
}

// Lấy tất cả participants từ dữ liệu
const allParticipants = Object.keys(participantsJson).map(id => ({
    id: participantsJson[id].id,
    qrcode: participantsJson[id].qrcode,
    name: participantsJson[id].name,
    company: participantsJson[id].company,
    stt: participantsJson[id].stt || participantsJson[id].qrcode,
    position: participantsJson[id].position,
    department: participantsJson[id].department,
    daily: participantsJson[id].daily,
    phongban: participantsJson[id].phongban,
    showroom: participantsJson[id].showroom,
    manv: participantsJson[id].manv || '00000000', // Mã nhân viên - mặc định 8 số 0
    chucvu: participantsJson[id].chucvu,
    type: participantsJson[id].type, // Loại khách hàng: IWPB hoặc non-IWPB
}));

// Danh sách participants đủ điều kiện quay
// Yêu cầu: Bỏ logic quay theo từng loại khách, tất cả giải quay trên toàn bộ danh sách khách Lucky Draw
// → Không lọc theo type hay theo loại giải thưởng nữa
let participants = [...allParticipants];

// Load danh sách người bị bỏ qua từ localStorage
let skippedIds = loadSkippedIds();
console.log(`Loaded ${skippedIds.length} người bị bỏ qua từ localStorage:`, skippedIds);

// Loại bỏ những người đã bị skip (không nhận giải) khỏi danh sách (so sánh id dạng string để tránh lệch kiểu)
function isSkipped(participantId) {
    return skippedIds.some(sid => String(sid) === String(participantId));
}
if (skippedIds.length > 0) {
    const beforeCount = participants.length;
    participants = participants.filter(p => !isSkipped(p.id));
    console.log(`Đã loại ${beforeCount - participants.length} người bị bỏ qua khỏi danh sách`);
}

console.log(`Tổng số khách đủ điều kiện quay: ${participants.length} / ${allParticipants.length}`);

// Người được gán trúng (assignee_id): tìm trong allParticipants để vẫn ra đúng dù họ đã nằm trong skippedIds
const predefinedId = assigneeId != null && assigneeId !== '' ? String(assigneeId).trim() : null;
let predefinedResult = predefinedId
    ? allParticipants.find(p => String(p.id) === String(predefinedId)) || null
    : null;
if (predefinedId && predefinedResult) {
    console.log('Đã gán người trúng (assignee_id):', predefinedResult.name, '(id:', predefinedResult.id, ')');
} else if (predefinedId && !predefinedResult) {
    console.warn('assignee_id =', predefinedId, 'không tìm thấy trong danh sách khách. Sẽ quay random.');
}

const value = parseInt(dataDiv.getAttribute('data-value'));
const numFlipCards = 8; // 8 ô số flip
const raffleTime = time * 1000;
let intervals = [];
let flipCards = [];
let finalResults = [];
let fireworkHtml = '<div class="before"></div><div class="after"></div>';

const boxContainer = document.getElementById('boxContainer');
const finalResultDiv = document.getElementById('finalResult');
const winnerDisplay = document.getElementById('winnerDisplay');
const winnerName = document.getElementById('winnerName');
const raffleSound = document.getElementById('raffleSound');
const victorySound = document.getElementById('victorySound');

// Tạo 5 flip cards
boxContainer.innerHTML = '';
flipCards = [];
for (let i = 0; i < numFlipCards; i++) {
    const card = document.createElement('div');
    card.className = 'flip-card';
    card.id = `flipCard${i}`;
    card.innerHTML = `
        <div class="flip-card-inner">
            <div class="flip-number">0</div>
        </div>
    `;
    boxContainer.appendChild(card);
    flipCards.push(card);
}

// Ẩn winner display ban đầu
winnerDisplay.classList.remove('show');

// Trạng thái: true khi vừa reset về số 0, cần nhấn Space lần nữa mới quay
let waitingForNextSpin = false;

// Trạng thái: true khi đang xử lý action, ngăn nhấn nút khác
let isProcessing = false;

// Disable tất cả các nút
function disableAllButtons() {
    isProcessing = true;
    document.getElementById('startButton').disabled = true;
    document.getElementById('cancelButton').disabled = true;
    document.getElementById('saveButton').disabled = true;
}

// Enable lại các nút (tuỳ theo trạng thái hiện tại)
function enableButtons() {
    isProcessing = false;
    const saveBtnBlock = document.getElementById('btn-save-block');
    
    if (saveBtnBlock.style.display === 'block') {
        // Đang hiển thị kết quả → enable nút Huỷ và Lưu
        document.getElementById('cancelButton').disabled = false;
        document.getElementById('saveButton').disabled = false;
        document.getElementById('startButton').disabled = true;
    } else {
        // Chưa có kết quả → enable nút Start
        document.getElementById('startButton').disabled = false;
        document.getElementById('cancelButton').disabled = true;
        document.getElementById('saveButton').disabled = true;
    }
}

// Xử lý tất cả phím Space ở đây
document.addEventListener('keydown', function(event) {
    if (event.keyCode === 32) { // Space
        event.preventDefault();
        event.stopImmediatePropagation(); // Ngăn các listener khác bắt event này
        
        // Nếu đang xử lý action khác thì bỏ qua
        if (isProcessing) {
            console.log('Đang xử lý, vui lòng chờ...');
            return;
        }
        
        const saveBtnBlock = document.getElementById('btn-save-block');
        const startButton = document.getElementById('startButton');
        
        // Trường hợp 1: Đang hiển thị kết quả (save block visible) → Reset về số 0
        if (saveBtnBlock.style.display === 'block') {
            disableAllButtons();
            hideSaveBtnBlock();
            waitingForNextSpin = true; // Đánh dấu đang chờ nhấn Space lần nữa
            return;
        }
        
        // Trường hợp 2: Vừa reset xong, nhấn Space lần 2 → Bắt đầu quay
        if (waitingForNextSpin) {
            waitingForNextSpin = false;
            disableAllButtons();
            startRaffle();
            return;
        }
        
        // Trường hợp 3: Bình thường, nhấn Space để bắt đầu quay
        if (!startButton.disabled && startButton.style.display !== 'none') {
            disableAllButtons();
            startRaffle();
        }
    }
});

function getRandomParticipant(excludeIds = []) {
    const available = participants.filter(p => !excludeIds.some(eid => String(eid) === String(p.id)));
    if (available.length === 0) {
        console.log('Không còn khách hàng đủ điều kiện để quay');
        return null;
    }
    const randomIndex = Math.floor(Math.random() * available.length);
    return available[randomIndex];
}

// Kiểm tra và hiển thị thông báo nếu không có khách đủ điều kiện
function checkEligibleParticipants() {
    if (participants.length === 0) {
        alert(`Không có khách hàng đủ điều kiện cho giải ${rewardOrderName}.\nVui lòng kiểm tra lại dữ liệu.`);
        document.getElementById('startButton').disabled = true;
        return false;
    }
    return true;
}

// Chạy kiểm tra khi load trang
checkEligibleParticipants();

function getRandomDigit() {
    return Math.floor(Math.random() * 10);
}

function padManv(manv, length = 8) {
    // Đảm bảo manv luôn có đủ số chữ số
    const str = String(manv || '0');
    return str.padStart(length, '0').slice(-length);
}

function shuffleFlipCards() {
    // Random số cho mỗi ô flip card
    flipCards.forEach(card => {
        const numberEl = card.querySelector('.flip-number');
        numberEl.textContent = getRandomDigit();
    });
}

function displayManv(manv) {
    const paddedManv = padManv(manv, numFlipCards);
    flipCards.forEach((card, index) => {
        const numberEl = card.querySelector('.flip-number');
        numberEl.textContent = paddedManv[index];
    });
}

function startRaffle() {
    // Disable tất cả nút khi bắt đầu quay
    disableAllButtons();
    
    finalResults = [];
    finalResultDiv.style.display = 'none';
    $('.pyro').html('');
    
    // Ẩn winner display
    winnerDisplay.classList.remove('show');
    winnerName.textContent = '';

    // Thêm class spinning cho tất cả flip cards
    flipCards.forEach(card => {
        card.classList.add('spinning');
        card.classList.remove('winner');
    });

    // Bắt đầu shuffle (chỉ dừng khi nhấn ESC)
    intervals[0] = setInterval(shuffleFlipCards, 80);
}

function stopShuffling(targetParticipant) {
    // Clear interval
    clearInterval(intervals[0]);
    intervals[0] = null;
    
    // Hiển thị mã nhân viên
    const manv = targetParticipant.manv || '00000000';
    displayManv(manv);

    // Xóa spinning, thêm winner
    flipCards.forEach(card => {
        card.classList.remove('spinning');
        card.classList.add('winner');
    });

    // Lưu kết quả
    finalResults[0] = {
        id: targetParticipant.id,
        qrcode: targetParticipant.qrcode,
        name: targetParticipant.name,
        company: targetParticipant.company,
        stt: targetParticipant.stt,
        position: targetParticipant.position,
        daily: targetParticipant.daily,
        showroom: targetParticipant.showroom,
        manv: targetParticipant.manv,
        chucvu: targetParticipant.chucvu,
    };

    // Hiển thị tên người trúng
    setTimeout(() => {
        winnerName.textContent = targetParticipant.name || '';
        winnerDisplay.classList.add('show');
        showFinalResult();
    }, 500);
}

function showFinalResult() {
    const finalString = finalResults.map(result => result.manv).join('');
    const finalIds = finalResults.map(result => result.id).join(', ');
    const finalName = finalResults.map(result => result.name).join('');

    console.log(`Final result: Mã NV ${finalString}, Tên: ${finalName} (IDs: ${finalIds})`);
    // $('.pyro').html(fireworkHtml);
    showSaveBtnBlock();
    
    // Enable nút Huỷ và Lưu sau khi hiển thị kết quả
    enableButtons();
}

function hideResult() {
    finalResultDiv.textContent = "";
    finalResultDiv.style.display = 'none';
    $('.pyro').html("");
    
    // Reset flip cards về 0
    flipCards.forEach(card => {
        card.classList.remove('winner', 'spinning');
        const numberEl = card.querySelector('.flip-number');
        numberEl.textContent = '0';
    });

    // Ẩn winner display
    winnerDisplay.classList.remove('show');
    winnerName.textContent = '';
}

function showSaveBtnBlock() {
    const saveBtnBlock = document.getElementById('btn-save-block');
    const startBtnBlock = document.getElementById('startButton');
    saveBtnBlock.style.display = 'block';
    startBtnBlock.style.display = 'none';
}

function hideSaveBtnBlock() {
    if (finalResults.length === 0) {
        enableButtons();
        return;
    }
    
    // Disable tất cả nút trong khi xử lý
    disableAllButtons();
    
    const saveBtnBlock = document.getElementById('btn-save-block');
    const startBtnBlock = document.getElementById('startButton');
    
    // Thêm ID người vừa trúng vào danh sách bị bỏ qua (không lên nhận giải)
    // Lưu vào localStorage để áp dụng cho tất cả các giải trong Lucky Draw này
    const skippedId = finalResults[0].id;
    skippedIds = addToSkippedIds(skippedId, finalResults[0].name);

    // Reset UI để quay lại
    saveBtnBlock.style.display = 'none';
    startBtnBlock.style.display = '';
    
    // Kiểm tra còn người đủ điều kiện không
    const remainingParticipants = participants.filter(p => !isSkipped(p.id));
    if (remainingParticipants.length === 0) {
        alert('Không còn khách hàng đủ điều kiện để quay!');
        isProcessing = false; // Reset trạng thái nhưng giữ nút disabled
    } else {
        console.log(`Còn ${remainingParticipants.length} người đủ điều kiện để quay`);
        // Enable nút Start để có thể quay tiếp
        enableButtons();
    }

    hideResult();
}

function saveRaffleResult() {
    if (finalResults.length === 0) {
        return;
    }

    // Disable tất cả nút khi đang lưu
    disableAllButtons();
    console.log('Đang lưu kết quả...');

    const clientIds = finalResults.map(result => result.id);
    const url = dataDiv.getAttribute('data-url');
    const rewardId = dataDiv.getAttribute('data-reward_id');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            client_ids: clientIds,
            reward_id: rewardId,
        }),
    })
    .then(response => response.json())
    .then(data => {
        console.log('Success:', data);

        if (data.status == "success" && data.status_code == 200) {
            const dataDiv = document.getElementById('data-clients');
            const value = parseInt(dataDiv.getAttribute('data-value'));
            let winnerCount = parseInt(dataDiv.getAttribute('data-winner_count'));
            winnerCount += finalResults.length;

            if (winnerCount < value) {
                location.reload();
            } else {
                location.reload();
            }
        } else {
            alert(data.message.message);
            // Enable lại nút nếu có lỗi
            enableButtons();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the raffle results.');
        // Enable lại nút nếu có lỗi
        enableButtons();
    });
}

// Hàm dừng quay khi nhấn ESC
function stopRaffleEarly() {
    // Nếu đang không quay (không có interval) thì bỏ qua
    if (!intervals[0]) {
        return;
    }
    setTimeout(() => {
        // Ưu tiên người được chọn trước (assignee_id), không có thì random
        const targetParticipant = predefinedResult || getRandomParticipant(skippedIds);
        if (targetParticipant) {
            stopShuffling(targetParticipant);
        } else {
            // Không có người đủ điều kiện, dừng quay và thông báo
            clearInterval(intervals[0]);
            intervals[0] = null;
            flipCards.forEach(card => {
                card.classList.remove('spinning');
            });
            alert('Không còn khách hàng đủ điều kiện để quay!');
            enableButtons();
        }
    }, 500);
}
