const dataDiv = document.getElementById('data-clients');
const time = dataDiv.getAttribute('data-time');
const assigneeId = dataDiv.getAttribute('data-assignee_id');
const participantsData = dataDiv.getAttribute('data-clients');
const participantsJson = JSON.parse(participantsData);
const participants = Object.keys(participantsJson).map(id => ({
    id: participantsJson[id].id,
    qrcode: participantsJson[id].qrcode,
    name: participantsJson[id].name,
    email: participantsJson[id].email,
    company: participantsJson[id].company,
    stt: participantsJson[id].stt || participantsJson[id].qrcode,
    position: participantsJson[id].position,
    department: participantsJson[id].department,
    daily: participantsJson[id].daily,
    phongban: participantsJson[id].phongban,
    showroom: participantsJson[id].showroom,
    manv: participantsJson[id].manv,
    chucvu: participantsJson[id].chucvu,
}));

// Set the predefined result here. Use null for random result
const predefinedId = parseInt(assigneeId) || null;
let predefinedResult = participants.find(participant => participant.id === predefinedId) || getRandomParticipant();

const value = parseInt(dataDiv.getAttribute('data-value')) || 1;
const numRaffleBoxes = Math.max(1, value); // Số người trúng = cột value của giải
const raffleTime = time*1000; // Adjust the raffle time here (in milliseconds)
let intervals = [];
let raffleBoxes = [];
let finalResults = [];
let previousSimpleParticipant;
let fireworkHtml = '<div class="before"></div><div class="after"></div>';
let respinInterval = null;
let respinBoxIndex = null;
let isRespinning = false; // Flag khóa khi đang quay lại một ô

const boxContainer = document.getElementById('boxContainer');
const finalResultDiv = document.getElementById('finalResult');
const raffleSound = document.getElementById('raffleSound');
const victorySound = document.getElementById('victorySound');

// Xóa các box cũ nếu có
boxContainer.innerHTML = '';
raffleBoxes = [];
for (let i = 0; i < numRaffleBoxes; i++) {
    const box = document.createElement('div');
    box.className = 'raffle-box';
    box.id = `raffleBox${i + 1}`;
    box.dataset.index = i;
    box.innerHTML = `<div class="raffle-name">?</div>`;
    boxContainer.appendChild(box);
    raffleBoxes.push(box);
}

// Click vào ô (khi đã có kết quả) để quay lại ô đó, loại người vắng khỏi danh sách
boxContainer.addEventListener('click', function(e) {
    const box = e.target.closest('.raffle-box');
    if (!box || document.getElementById('btn-save-block').style.display !== 'block') return;
    
    // Khóa: nếu đang quay lại một ô khác, không cho click
    if (isRespinning && respinBoxIndex !== null) {
        return;
    }
    
    const index = parseInt(box.dataset.index, 10);
    if (isNaN(index) || index < 0 || index >= raffleBoxes.length) return;

    // Nếu ô này đang quay, không làm gì (chờ tự động dừng)
    if (box.classList.contains('spinning')) {
        return;
    }

    // Khóa: đánh dấu đang quay lại
    isRespinning = true;
    respinBoxIndex = index;
    const excludeIds = finalResults.map(r => r.id);

    box.classList.remove('winner');
    box.classList.add('spinning');
    
    // Khóa nút Lưu khi đang quay lại
    const saveButton = document.getElementById('saveButton');
    if (saveButton) saveButton.disabled = true;
    
    respinInterval = setInterval(function() {
        shuffleNames(box, excludeIds);
    }, 100);

    // Tự động dừng sau 5 giây
    setTimeout(function() {
        if (respinInterval && respinBoxIndex === index) {
            clearInterval(respinInterval);
            respinInterval = null;
            const target = getRandomParticipant(excludeIds);
            if (target) {
                finalResults[index] = {
                    id: target.id,
                    qrcode: target.qrcode,
                    name: target.name,
                    company: target.company,
                    stt: target.stt,
                    position: target.position,
                    daily: target.daily,
                    showroom: target.showroom,
                    manv: target.manv,
                    chucvu: target.chucvu,
                };
                box.classList.remove('spinning');
                box.classList.add('winner');
                box.innerHTML = `<div class="raffle-name">${target.email || ''}</div>`;
            }
            respinBoxIndex = null;
            isRespinning = false; // Mở khóa sau khi dừng
            
            // Mở khóa nút Lưu sau khi dừng
            const saveButton = document.getElementById('saveButton');
            if (saveButton) saveButton.disabled = false;
        }
    }, 5000);
});

// Bỏ event listener Space - không cho reset khi đã có kết quả

function getRandomParticipant(excludeIds = []) {
    const available = participants.filter(p => !excludeIds.includes(p.id));
    if (available.length === 0) return null;
    const randomIndex = Math.floor(Math.random() * available.length);
    return available[randomIndex];
}

function shuffleNames(box, excludeIds = []) {
    const randomParticipant = getRandomParticipant(excludeIds);
    if (!randomParticipant) return;
    const emailDisplay = randomParticipant.email || '?';
    box.innerHTML = `<div class="raffle-name">${emailDisplay}</div>`;
}

function stopShuffling(box, targetParticipant, interval, index) {
    clearInterval(interval);
    box.classList.remove('spinning');
    box.classList.add('winner');

    // Chỉ hiển thị EMAIL khi dừng
    const emailText = targetParticipant.email || '';
    
    box.innerHTML = `<div class="raffle-name">${emailText}</div>`;
    
    finalResults[index] = {
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
    if (finalResults.length === raffleBoxes.length) {
        showFinalResult();
    }
}

function startRaffle() {
    finalResults = [];
    finalResultDiv.style.display = 'none';
    finalResultDiv.textContent = '';
    document.getElementById('startButton').disabled = true; // Disable the button
    boxContainer.classList.remove('raffle-result-visible');
    $('.pyro').html('');
    
    // Reset flag re-spin khi bắt đầu quay mới
    if (respinInterval) {
        clearInterval(respinInterval);
        respinInterval = null;
    }
    respinBoxIndex = null;
    isRespinning = false;

    let selectedIds = [];
    for (let i = 0; i < raffleBoxes.length; i++) {
        let targetParticipant = getRandomParticipant(selectedIds);
        if (targetParticipant) selectedIds.push(targetParticipant.id);
        let box = raffleBoxes[i];
        box.classList.add('spinning');
        box.classList.remove('winner');

        function shuffle() {
            shuffleNames(box);
        }

        intervals[i] = setInterval(shuffle, 100);
    }

    // Tự động dừng sau thời gian từ cột time (raffleTime)
    setTimeout(() => {
        let selectedIds = [];
        intervals.forEach((interval, i) => {
            clearInterval(interval);
            const box = raffleBoxes[i];
            const targetParticipant = getRandomParticipant(selectedIds);
            if (targetParticipant) selectedIds.push(targetParticipant.id);
            stopShuffling(box, targetParticipant, interval, i);
        });
        showFinalResult();
    }, raffleTime);
}

function showFinalResult() {
    document.getElementById('startButton').disabled = false; // Enable the button
    const finalString = finalResults.map(result => result.qrcode).join('');
    const finalIds = finalResults.map(result => result.id).join(', ');
    const finalName = finalResults.map(result => result.name).join('');

    console.log(`Final result: ${finalString} (IDs: ${finalIds})`);
    $('.pyro').html(fireworkHtml);
    showSaveBtnBlock();
    boxContainer.classList.add('raffle-result-visible');
}

function hideResult() {
    finalResultDiv.textContent = "";
    finalResultDiv.style.display = 'none';
    $('.pyro').html("");
    raffleBoxes.forEach(box => {
        box.classList.remove('winner', 'spinning');
        box.innerHTML = `<div class="raffle-name">?</div>`;
    });
}

function showSaveBtnBlock() {
    const saveBtnBlock = document.getElementById('btn-save-block');
    const startBtnBlock = document.getElementById('startButton');
    saveBtnBlock.style.display = 'block';
    startBtnBlock.style.display = 'none';
}

function hideSaveBtnBlock() {
    if (finalResults.length === 0) {
        return;
    }
    const saveBtnBlock = document.getElementById('btn-save-block');
    const startBtnBlock = document.getElementById('startButton');
    const finalId = parseInt(finalResults.map(result => result.id).join(''));

    fetch(`/clients/${finalId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        console.log(response);
        if (response.ok) {
            console.log('Khách hàng đã được xóa thành công');
        } else {
            console.error('Có lỗi xảy ra khi xóa khách hàng');
        }
    })
    .catch(error => {
        console.error('Lỗi kết nối:', error);
    });

    saveBtnBlock.style.display = 'none';
    startBtnBlock.style.display = '';
    document.getElementById('startButton').disabled = true;
    document.getElementById('saveButton').disabled = true;
    location.reload();

    hideResult();
}

function saveRaffleResult() {
    if (finalResults.length === 0) {
        return;
    }
    
    // Khóa: không cho lưu khi đang quay lại một ô
    if (isRespinning && respinInterval) {
        return;
    }

    // Lấy danh sách các id người trúng giải
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
            // Lấy value và winner_count từ data attribute
            const dataDiv = document.getElementById('data-clients');
            const value = parseInt(dataDiv.getAttribute('data-value'));
            let winnerCount = parseInt(dataDiv.getAttribute('data-winner_count'));
            winnerCount += finalResults.length; // Tăng lên theo số lượng người vừa trúng

            if (winnerCount < value) {
                // Chưa đủ, chỉ reload lại trang để quay tiếp giải hiện tại (background giữ nguyên)
                location.reload();
            } else {
                // Đủ, reload toàn trang để chuyển sang giải tiếp theo
                location.reload();
            }
        } else {
            alert(data.message.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the raffle results.');
    });
}

// Bỏ hàm stopRaffleEarly - không dùng ESC nữa, chỉ tự động dừng sau 5 giây
