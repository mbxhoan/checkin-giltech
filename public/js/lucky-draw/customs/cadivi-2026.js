const dataDiv = document.getElementById('data-clients');
const time = dataDiv.getAttribute('data-time');
const parsedTime = parseInt(time) || 0;
// Nếu time < 100, giả định là giây và chuyển sang milliseconds
// Nếu time >= 100, giả định đã là milliseconds
const timeInMs = parsedTime < 100 ? parsedTime * 1000 : parsedTime;
const raffleTime = timeInMs >= 1000 ? timeInMs : 1000; // Tối thiểu 1 giây mỗi ô
const boxDelay = 300; // Độ trễ giữa các ô (ms)

const assigneeId = dataDiv.getAttribute('data-assignee_id');
const assigneeIdsAttr = dataDiv.getAttribute('data-assignee_ids') || '';
const participantsData = dataDiv.getAttribute('data-clients');
const participantsJson = JSON.parse(participantsData);
const participants = Object.keys(participantsJson).map(id => ({
    id: participantsJson[id].id,
    qrcode: participantsJson[id].qrcode,
    name: participantsJson[id].name,
    manv: participantsJson[id].manv || participantsJson[id].ma_nv || '',
    congty: participantsJson[id].congty,
    position: participantsJson[id].position,
    department: participantsJson[id].department,
    phone: participantsJson[id].phone,
    phongban: participantsJson[id].phongban,
    type: participantsJson[id].type,
    don_vi_mot: participantsJson[id].don_vi_mot || '',
    don_vi_hai: participantsJson[id].don_vi_hai || '',
}));

console.log('Total participants:', participants.length);

// Company types
const COMPANY_TYPES = ['Newtecons', 'Ricons', 'SOL E&C'];

const predefinedId = parseInt(assigneeId) || null;
let predefinedResult = participants.find(participant => participant.id === predefinedId) || null;

// Danh sách nhiều người được gán trước (multi assignees)
const predefinedIds = assigneeIdsAttr
    .split(',')
    .map(id => parseInt(id.trim()))
    .filter(id => !isNaN(id));

// Đảm bảo id đơn (assignee_id) cũng nằm trong danh sách nếu có
if (predefinedId && !predefinedIds.includes(predefinedId)) {
    predefinedIds.unshift(predefinedId);
}

const predefinedResults = predefinedIds
    .map(id => participants.find(p => p.id === id))
    .filter(p => !!p);

const numWinners = parseInt(dataDiv.getAttribute('data-value')) || 1;
let currentInterval = null;
let raffleBoxes = [];
let finalResults = [];
let fireworkHtml = '<div class="before"></div><div class="after"></div>';
let isSpinning = false;
let isRaffleComplete = false;
let currentBoxIndex = 0;
let availableParticipants = [];
let allWinners = [];
let excludedParticipants = []; // Danh sách người bị loại (không nhận giải)
let reSpinningBoxIndex = null; // Box đang được quay lại

const boxContainer = document.getElementById('boxContainer');
const finalResultDiv = document.getElementById('finalResult');
const raffleSound = document.getElementById('raffleSound');
const victorySound = document.getElementById('victorySound');

// CADIVI prize layouts:
//   1/2/4/6 winners → single horizontal row
//   15 winners (khuyến khích) → 2 rows: 8 top + 7 bottom
//   other counts → original 2-column layout (fallback)
const cadiviCountClasses = { 1: 'count-1', 2: 'count-2', 4: 'count-4', 6: 'count-6', 15: 'count-15' };
const useCadiviLayout = !!cadiviCountClasses[numWinners];
const isConsolationLayout = numWinners === 15;
let topRow = null;
let bottomRow = null;
let singleRow = null;

if (useCadiviLayout) {
    const panel = document.querySelector('.winners-panel');
    panel.classList.add('cadivi-layout', cadiviCountClasses[numWinners]);

    if (isConsolationLayout) {
        topRow = document.createElement('div');
        topRow.className = 'raffle-row row-top';
        bottomRow = document.createElement('div');
        bottomRow.className = 'raffle-row row-bottom';
        boxContainer.appendChild(topRow);
        boxContainer.appendChild(bottomRow);
    } else {
        singleRow = document.createElement('div');
        singleRow.className = 'raffle-row row-single';
        boxContainer.appendChild(singleRow);
    }
}

// Create boxes - all start empty
for (let i = 0; i < numWinners; i++) {
    const box = document.createElement('div');
    box.className = 'raffle-box';
    box.id = `raffleBox${i + 1}`;
    box.innerHTML = ''; // Empty at start
    box.dataset.boxIndex = i; // Store index for click handler

    if (useCadiviLayout) {
        if (isConsolationLayout) {
            (i < 8 ? topRow : bottomRow).appendChild(box);
        } else {
            singleRow.appendChild(box);
        }
    } else {
        boxContainer.appendChild(box);
    }
    raffleBoxes.push(box);

    // Add click handler for re-spin
    box.addEventListener('click', function() {
        const boxIndex = parseInt(this.dataset.boxIndex);
        handleBoxClick(boxIndex);
    });
}

// Keyboard event handler
document.addEventListener('keydown', function(event) {
    // Space bar - Start spinning
    if (event.keyCode === 32) {
        event.preventDefault();
        if (!isSpinning && !isRaffleComplete) {
            // Defer heavy work to next frame so keypress paints immediately (better INP)
            requestAnimationFrame(() => startRaffle());
        } else if (isRaffleComplete && !isSpinning && document.getElementById('btn-save-block').style.display === 'block') {
            hideSaveBtnBlock();
        }
    }
    
    // ESC - Stop current box and move to next, or stop re-spin
    if (event.keyCode === 27) {
        event.preventDefault();
        if (isSpinning) {
            if (reSpinningBoxIndex !== null) {
                // Stop re-spin
                stopReSpinBox(reSpinningBoxIndex);
            } else {
                // Stop normal spin
                stopCurrentBox();
            }
        }
    }
    
    // Enter - Save results
    if (event.keyCode === 13) {
        event.preventDefault();
        if (document.getElementById('btn-save-block').style.display === 'block' && !isSpinning) {
            document.getElementById('saveButton').click();
        }
    }
});

function getRandomFromAvailable() {
    if (availableParticipants.length === 0) return null;
    const randomIndex = Math.floor(Math.random() * availableParticipants.length);
    return availableParticipants[randomIndex];
}

function ensureShuffleEl(box) {
    let el = box.querySelector('.shuffle-name');
    if (!el) {
        box.innerHTML = '';
        el = document.createElement('div');
        el.className = 'shuffle-name';
        box.appendChild(el);
    }
    return el;
}

function shuffleCurrentBox() {
    const box = raffleBoxes[currentBoxIndex];
    const randomParticipant = getRandomFromAvailable();
    if (randomParticipant && box) {
        ensureShuffleEl(box).textContent = randomParticipant.name;
    }
}

function startRaffle() {
    if (participants.length === 0) {
        console.log('No participants available');
        return;
    }
    
    console.log('Starting raffle...');
    isSpinning = true;
    isRaffleComplete = false;
    currentBoxIndex = 0;
    excludedParticipants = []; // Reset excluded list
    reSpinningBoxIndex = null;
    availableParticipants = [...participants];
    
    // Pre-determine all winners
    allWinners = getWinnersDistributedByCompany(numWinners);
    
    // Nếu có danh sách người được cơ cấu trước, ensure tất cả đều nằm trong allWinners
    if (predefinedResults.length > 0 && allWinners.length > 0) {
        // Loại trùng lặp theo id
        const uniquePredefined = [];
        const seen = new Set();
        predefinedResults.forEach(p => {
            if (p && !seen.has(p.id)) {
                seen.add(p.id);
                uniquePredefined.push(p);
            }
        });

        // Gán lần lượt vào các ô đầu tiên
        uniquePredefined.forEach((p, index) => {
            if (index < allWinners.length) {
                allWinners[index] = p;
            }
        });
    }
    
    if (finalResultDiv) {
        finalResultDiv.style.display = 'none';
        finalResultDiv.textContent = '';
    }
    
    document.getElementById('startButton').disabled = true;
    $('.pyro').html('');

    // Initialize finalResults array with nulls
    finalResults = new Array(numWinners).fill(null);

    // Reset all boxes to empty
    raffleBoxes.forEach((box, index) => {
        box.classList.remove('shuffling', 'winner', 'clickable');
        box.innerHTML = ''; // Empty
    });
    
    // Start spinning first box
    spinCurrentBox();
}

function spinCurrentBox() {
    if (currentBoxIndex >= numWinners || currentBoxIndex >= allWinners.length) {
        finishRaffle();
        return;
    }
    
    const box = raffleBoxes[currentBoxIndex];
    box.classList.add('shuffling');
    
    // Play sound
    try {
        raffleSound.currentTime = 0;
        raffleSound.playbackRate = 2;
        raffleSound.play();
    } catch(e) {
        console.log('Sound error:', e);
    }
    
    // Start shuffling animation for current box only
    currentInterval = setInterval(() => {
        shuffleCurrentBox();
    }, 80);
    
    console.log(`Box ${currentBoxIndex + 1} spinning...`);
    
    // Auto stop after raffleTime
    setTimeout(() => {
        if (isSpinning && currentInterval) {
            stopCurrentBox();
        }
    }, raffleTime);
}

function stopCurrentBox() {
    if (!isSpinning || currentBoxIndex >= numWinners) return;
    
    const box = raffleBoxes[currentBoxIndex];
    const winner = allWinners[currentBoxIndex];
    
    // Clear interval
    if (currentInterval) {
        clearInterval(currentInterval);
        currentInterval = null;
    }
    
    // Stop sound
    raffleSound.pause();
    
    // Show winner
    box.classList.remove('shuffling');
    box.classList.add('winner');
    
    if (winner) {
        box.innerHTML = `
            <div class="winner-name">${winner.name || ''}</div>
            <div class="winner-code">${winner.don_vi_mot || ''}</div>
            <div class="winner-code">${winner.don_vi_hai || ''}</div>
        `;

        // Use index-based assignment to maintain proper positioning
        finalResults[currentBoxIndex] = winner;
        
        // Remove winner from available participants
        availableParticipants = availableParticipants.filter(p => p.id !== winner.id);
    } else {
        // Set null to maintain array index alignment
        finalResults[currentBoxIndex] = null;
    }
    
    console.log(`Box ${currentBoxIndex + 1} stopped: ${winner ? winner.name : 'No winner'}`);
    
    // Move to next box
    currentBoxIndex++;
    
    if (currentBoxIndex < numWinners && currentBoxIndex < allWinners.length) {
        // Start next box after a short delay
        setTimeout(() => {
            spinCurrentBox();
        }, boxDelay);
    } else {
        // All boxes done
        finishRaffle();
    }
}

function finishRaffle() {
    console.log('Finishing raffle...');
    isSpinning = false;
    isRaffleComplete = true;
    
    // Clear any remaining interval
    if (currentInterval) {
        clearInterval(currentInterval);
        currentInterval = null;
    }
    
    // Stop sound
    raffleSound.pause();
    
    // Add clickable class to all winner boxes
    raffleBoxes.forEach((box, index) => {
        if (finalResults[index]) {
            box.classList.add('clickable');
        }
    });
    
    // Update available participants for potential re-spins
    updateAvailableParticipants();
    
    // Show fireworks and save button
    $('.pyro').html(fireworkHtml);
    showSaveBtnBlock();
    
    // Log results
    const validResults = finalResults.filter(r => r !== null);
    const finalIds = validResults.map(r => r.id).join(', ');
    const finalNames = validResults.map(r => r.name).join(', ');
    console.log(`Winners: ${finalNames} (IDs: ${finalIds})`);
    
    // Play victory sound
    setTimeout(() => {
        try {
            victorySound.currentTime = 0;
            victorySound.playbackRate = 1;
            victorySound.play();
        } catch(e) {
            console.log('Victory sound error:', e);
        }
    }, 500);
    
    setTimeout(() => {
        victorySound.pause();
    }, 10000);
}

function getRandomWinners(count) {
    const availableList = [...participants];
    const winners = [];

    for (let i = 0; i < count; i++) {
        if (availableList.length === 0) break;
        const randomIndex = Math.floor(Math.random() * availableList.length);
        winners.push(availableList[randomIndex]);
        availableList.splice(randomIndex, 1);
    }

    return winners;
}

function getWinnersDistributedByCompany(totalCount) {
    const winnersPerCompany = Math.floor(totalCount / COMPANY_TYPES.length);
    const winners = [];
    
    // Try to distribute by company type
    COMPANY_TYPES.forEach(type => {
        const companyParticipants = participants.filter(p => 
            p.type && p.type.toLowerCase() === type.toLowerCase()
        );
        
        if (companyParticipants.length > 0) {
            const shuffled = [...companyParticipants].sort(() => Math.random() - 0.5);
            const selected = shuffled.slice(0, winnersPerCompany);
            winners.push(...selected);
        }
    });
    
    // Fallback: if no winners found by type, use random selection
    if (winners.length === 0) {
        console.log('No participants with type found, using random selection');
        return getRandomWinners(totalCount);
    }
    
    // If we got fewer winners than needed, fill with random
    if (winners.length < totalCount) {
        const winnerIds = winners.map(w => w.id);
        const remaining = participants.filter(p => !winnerIds.includes(p.id));
        const shuffledRemaining = [...remaining].sort(() => Math.random() - 0.5);
        const additionalWinners = shuffledRemaining.slice(0, totalCount - winners.length);
        winners.push(...additionalWinners);
    }
    
    // Shuffle the final winners array to mix up company order
    const shuffledWinners = [...winners].sort(() => Math.random() - 0.5);
    
    return shuffledWinners;
}

function showSaveBtnBlock() {
    const saveBtnBlock = document.getElementById('btn-save-block');
    const startBtnBlock = document.getElementById('startButton');
    saveBtnBlock.style.display = 'block';
    startBtnBlock.style.display = 'none';
}

function hideSaveBtnBlock() {
    const validResults = finalResults.filter(r => r !== null);
    if (validResults.length === 0) return;
    
    const saveBtnBlock = document.getElementById('btn-save-block');
    const startBtnBlock = document.getElementById('startButton');
    const finalId = parseInt(validResults.map(result => result.id).join(''));

    fetch(`/clients/${finalId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (response.ok) {
            console.log('Client deleted successfully');
        }
    })
    .catch(error => {
        console.error('Connection error:', error);
    });

    saveBtnBlock.style.display = 'none';
    startBtnBlock.style.display = '';
    document.getElementById('startButton').disabled = true;
    document.getElementById('saveButton').disabled = true;
    location.reload();
}

function saveRaffleResult() {
    const validResults = finalResults.filter(r => r !== null);
    if (validResults.length === 0) return;

    const url = dataDiv.getAttribute('data-url');
    const rewardId = dataDiv.getAttribute('data-reward_id');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const clientIds = validResults.map(winner => winner.id);

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
        if (data.status === "success") {
            location.reload();
        } else {
            alert(data.message || 'Error saving results');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving raffle results');
    });
}

function hideResult() {
    if (finalResultDiv) {
        finalResultDiv.textContent = "";
        finalResultDiv.style.display = 'none';
    }
    $('.pyro').html("");
    raffleBoxes.forEach(box => {
        box.classList.remove('winner', 'shuffling', 'clickable');
        box.innerHTML = '';
    });
}

// Handle click on a winner box to re-spin
function handleBoxClick(boxIndex) {
    // Only allow re-spin when raffle is complete and not currently spinning
    if (!isRaffleComplete || isSpinning || reSpinningBoxIndex !== null) {
        return;
    }
    
    const box = raffleBoxes[boxIndex];
    const currentWinner = finalResults[boxIndex];
    
    if (!currentWinner) {
        console.log('No winner in this box to replace');
        return;
    }
    
    console.log(`Re-spinning box ${boxIndex + 1}, excluding: ${currentWinner.name}`);
    
    // Add current winner to excluded list
    excludedParticipants.push(currentWinner);
    
    // Remove from finalResults
    finalResults[boxIndex] = null;
    
    // Update available participants (exclude all winners and excluded participants)
    updateAvailableParticipants();
    
    if (availableParticipants.length === 0) {
        alert('Không còn người tham gia để quay lại!');
        // Restore the winner if no participants available
        excludedParticipants.pop();
        finalResults[boxIndex] = currentWinner;
        return;
    }
    
    // Start re-spin for this specific box
    reSpinBox(boxIndex);
}

// Update available participants list
function updateAvailableParticipants() {
    const winnerIds = finalResults.filter(w => w !== null).map(w => w.id);
    const excludedIds = excludedParticipants.map(p => p.id);
    
    availableParticipants = participants.filter(p => 
        !winnerIds.includes(p.id) && !excludedIds.includes(p.id)
    );
    
    console.log(`Available participants: ${availableParticipants.length}`);
}

// Re-spin a specific box
function reSpinBox(boxIndex) {
    reSpinningBoxIndex = boxIndex;
    isSpinning = true;
    
    const box = raffleBoxes[boxIndex];
    box.classList.remove('winner', 'clickable');
    box.classList.add('shuffling');
    
    // Hide fireworks during re-spin
    $('.pyro').html('');
    
    // Play sound
    try {
        raffleSound.currentTime = 0;
        raffleSound.playbackRate = 2;
        raffleSound.play();
    } catch(e) {
        console.log('Sound error:', e);
    }
    
    // Start shuffling animation
    currentInterval = setInterval(() => {
        const randomParticipant = getRandomFromAvailable();
        if (randomParticipant && box) {
            ensureShuffleEl(box).textContent = randomParticipant.name;
        }
    }, 80);
    
    console.log(`Box ${boxIndex + 1} re-spinning...`);
    
    // Auto stop after raffleTime
    setTimeout(() => {
        if (isSpinning && currentInterval && reSpinningBoxIndex === boxIndex) {
            stopReSpinBox(boxIndex);
        }
    }, raffleTime);
}

// Stop re-spinning a specific box
function stopReSpinBox(boxIndex) {
    if (reSpinningBoxIndex !== boxIndex) return;
    
    const box = raffleBoxes[boxIndex];
    
    // Clear interval
    if (currentInterval) {
        clearInterval(currentInterval);
        currentInterval = null;
    }
    
    // Stop sound
    raffleSound.pause();
    
    // Select new random winner
    if (availableParticipants.length > 0) {
        const randomIndex = Math.floor(Math.random() * availableParticipants.length);
        const newWinner = availableParticipants[randomIndex];
        
        // Update finalResults
        finalResults[boxIndex] = newWinner;
        
        // Show winner
        box.classList.remove('shuffling');
        box.classList.add('winner', 'clickable');
        
        box.innerHTML = `
            <div class="winner-name">${newWinner.name || ''}</div>
            <div class="winner-code">${newWinner.don_vi_mot || ''}</div>
            <div class="winner-code">${newWinner.don_vi_hai || ''}</div>
        `;
        
        // Update available participants
        updateAvailableParticipants();
        
        console.log(`Box ${boxIndex + 1} new winner: ${newWinner.name}`);
    }
    
    // Reset re-spin state
    reSpinningBoxIndex = null;
    isSpinning = false;
    
    // Show fireworks again
    $('.pyro').html(fireworkHtml);
    
    // Play victory sound
    try {
        victorySound.currentTime = 0;
        victorySound.playbackRate = 1;
        victorySound.play();
    } catch(e) {
        console.log('Victory sound error:', e);
    }
    
    setTimeout(() => {
        victorySound.pause();
    }, 5000);
    
    // Log updated results
    const validResults = finalResults.filter(r => r !== null);
    console.log(`Updated winners: ${validResults.map(r => r.name).join(', ')}`);
}
