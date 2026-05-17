const dataDiv = document.getElementById('data-clients');
const time = dataDiv.getAttribute('data-time');
const assigneeId = dataDiv.getAttribute('data-assignee_id');
const participantsData = dataDiv.getAttribute('data-clients');
const participantsJson = JSON.parse(participantsData);
const participants = Object.keys(participantsJson).map(id => ({
    id: participantsJson[id].id,
    qrcode: participantsJson[id].qrcode,
    name: participantsJson[id].name,
    // Công ty - thử nhiều trường
    company: participantsJson[id].company,
    stt: participantsJson[id].stt || participantsJson[id].qrcode, // Số thứ tự, fallback to qrcode
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

const value = parseInt(dataDiv.getAttribute('data-value'));
const numRaffleBoxes = 1; // Chỉ quay 1 người mỗi lần
const raffleTime = time*1000; // Adjust the raffle time here (in milliseconds)
let intervals = [];
let raffleBoxes = [];
let finalResults = [];
let previousSimpleParticipant;
let fireworkHtml = '<div class="before"></div><div class="after"></div>';

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
    box.innerHTML = `
        <div class="raffle-name">?</div>
        <div class="raffle-company"></div>
    `;
    boxContainer.appendChild(box);
    raffleBoxes.push(box);
}
document.addEventListener('keydown', function(event) {
            if (event.keyCode === 32 && document.getElementById('btn-save-block').style.display === 'block') {
                event.preventDefault();
                hideSaveBtnBlock();
            }
        });

function getRandomParticipant(excludeIds = []) {
    const available = participants.filter(p => !excludeIds.includes(p.id));
    if (available.length === 0) return null;
    const randomIndex = Math.floor(Math.random() * available.length);
    return available[randomIndex];
}

function shuffleNames(box) {
    const randomParticipant = getRandomParticipant();
    if (!randomParticipant) return;
    // Hiển thị NAME khi đang quay
    const nameDisplay = randomParticipant.name || '?';
    box.innerHTML = `
        <div class="raffle-name">${nameDisplay}</div>
        <div class="raffle-company"></div>
    `;
}

function stopShuffling(box, targetParticipant, interval, index) {
    clearInterval(interval);
    box.classList.remove('spinning');
    box.classList.add('winner');

    // Hiển thị NAME lớn ở trên, COMPANY ở dưới
    const nameText = targetParticipant.name || '';
    const companyText = targetParticipant.company || '';
    
    box.innerHTML = `
        <div class="raffle-name">${nameText}</div>
        <div class="raffle-company">${companyText}</div>
    `;
    
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
    $('.pyro').html('');

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
}

function showFinalResult() {
    document.getElementById('startButton').disabled = false; // Enable the button
    const finalString = finalResults.map(result => result.qrcode).join('');
    const finalIds = finalResults.map(result => result.id).join(', ');
    const finalName = finalResults.map(result => result.name).join('');

    console.log(`Final result: ${finalString} (IDs: ${finalIds})`);
    $('.pyro').html(fireworkHtml);
    showSaveBtnBlock();
}

function hideResult() {
    finalResultDiv.textContent = "";
    finalResultDiv.style.display = 'none';
    $('.pyro').html("");
    raffleBoxes.forEach(box => {
        box.classList.remove('winner', 'spinning');
        box.innerHTML = `
            <div class="raffle-name">?</div>
            <div class="raffle-company"></div>
        `;
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

// hàm dừng quay khi nhấn esc
function stopRaffleEarly() {
    // Clear all intervals
    setTimeout(() => {
        let selectedIds = [];
        intervals.forEach((interval, i) => {
            clearInterval(interval);
            const box = raffleBoxes[i];
            const targetParticipant = getRandomParticipant(selectedIds);
            if (targetParticipant) selectedIds.push(targetParticipant.id);
            stopShuffling(box, targetParticipant, interval, i);
        });
        // Show the result
        showFinalResult();
    }, 1000);
}
