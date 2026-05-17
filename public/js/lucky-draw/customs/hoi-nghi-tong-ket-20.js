const dataDiv = document.getElementById('data-clients');
const time = dataDiv.getAttribute('data-time');
const parsedTime = parseInt(time) || 0;
const raffleTime = parsedTime >= 5000 ? parsedTime : 5000; // Minimum 5 seconds
const assigneeId = dataDiv.getAttribute('data-assignee_id');
const participantsData = dataDiv.getAttribute('data-clients');
const participantsJson = JSON.parse(participantsData);
const participants = Object.keys(participantsJson).map(id => ({
    id: participantsJson[id].id,
    qrcode: participantsJson[id].qrcode,
    name: participantsJson[id].name,
    congty: participantsJson[id].congty,
    position: participantsJson[id].position,
    department: participantsJson[id].department,
    phone: participantsJson[id].phone,
    phongban: participantsJson[id].phongban,
    type: participantsJson[id].type,
}));

console.log('Total participants:', participants.length);

// Company types
const COMPANY_TYPES = ['Newtecons', 'Ricons', 'SOL E&C'];

const predefinedId = parseInt(assigneeId) || null;
let predefinedResult = participants.find(participant => participant.id === predefinedId) || null;

const numWinners = parseInt(dataDiv.getAttribute('data-value')) || 1;
let intervals = [];
let raffleBoxes = [];
let finalResults = [];
let fireworkHtml = '<div class="before"></div><div class="after"></div>';
let isSpinning = false;
let isRaffleComplete = false;
let autoStopTimeout = null;

const boxContainer = document.getElementById('boxContainer');
const finalResultDiv = document.getElementById('finalResult');
const raffleSound = document.getElementById('raffleSound');
const victorySound = document.getElementById('victorySound');

// Add prize level class based on number of winners
// Giải nhất: 3 ô (1 hàng)
// Giải nhì: 6 ô (3+3)
// Giải ba: 9 ô (5+4)
if (numWinners <= 3) {
    boxContainer.classList.add('prize-first');
} else if (numWinners <= 6) {
    boxContainer.classList.add('prize-second');
} else {
    boxContainer.classList.add('prize-third');
}

// Create boxes
for (let i = 0; i < numWinners; i++) {
    const box = document.createElement('div');
    box.className = 'raffle-box';
    box.id = `raffleBox${i + 1}`;
    box.innerHTML = '<div class="shuffle-name">?</div>';
    boxContainer.appendChild(box);
    raffleBoxes.push(box);
}

// Keyboard event handler
document.addEventListener('keydown', function(event) {
    // Space bar - Start spinning
    if (event.keyCode === 32) {
        event.preventDefault();
        if (!isSpinning && !isRaffleComplete) {
            startRaffle();
        } else if (isRaffleComplete && document.getElementById('btn-save-block').style.display === 'block') {
            hideSaveBtnBlock();
        }
    }
    
    // ESC - Stop spinning and show results
    if (event.keyCode === 27) {
        event.preventDefault();
        if (isSpinning) {
            stopRaffleEarly();
        }
    }
    
    // Enter - Save results
    if (event.keyCode === 13) {
        event.preventDefault();
        if (document.getElementById('btn-save-block').style.display === 'block') {
            document.getElementById('saveButton').click();
        }
    }
});

function getRandomParticipant() {
    if (participants.length === 0) return null;
    const randomIndex = Math.floor(Math.random() * participants.length);
    return participants[randomIndex];
}

function shuffleNames(box) {
    const randomParticipant = getRandomParticipant();
    if (randomParticipant) {
        box.innerHTML = `<div class="shuffle-name">${randomParticipant.name}</div>`;
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
    finalResults = [];
    intervals = [];
    
    if (finalResultDiv) {
        finalResultDiv.style.display = 'none';
        finalResultDiv.textContent = '';
    }
    
    document.getElementById('startButton').disabled = true;
    $('.pyro').html('');

    raffleBoxes.forEach(box => {
        box.classList.add('shuffling');
        box.classList.remove('winner');
        
        const shuffleInterval = setInterval(() => {
            shuffleNames(box);
        }, 80);
        
        intervals.push(shuffleInterval);
    });
    
    // Play sound
    try {
        raffleSound.currentTime = 0;
        raffleSound.playbackRate = 2;
        raffleSound.play();
    } catch(e) {
        console.log('Sound error:', e);
    }
    
    // Auto stop after raffleTime (default 5 seconds)
    autoStopTimeout = setTimeout(() => {
        if (isSpinning) {
            console.log('Auto stopping after ' + raffleTime + 'ms');
            stopRaffleEarly();
        }
    }, raffleTime);
}

function stopRaffleEarly() {
    if (!isSpinning) return;
    
    console.log('Stopping raffle...');
    isSpinning = false;
    
    // Clear auto stop timeout if manually stopped
    if (autoStopTimeout) {
        clearTimeout(autoStopTimeout);
        autoStopTimeout = null;
    }
    
    // Clear all intervals
    intervals.forEach(interval => clearInterval(interval));
    intervals = [];
    
    // Stop sound
    raffleSound.pause();
    
    // Get winners
    const winners = getWinnersDistributedByCompany(numWinners);
    
    // If predefinedResult exists, ensure it's included
    if (predefinedResult && winners.length > 0 && !winners.find(p => p.id === predefinedResult.id)) {
        winners[0] = predefinedResult;
    }
    
    showWinners(winners);
}

function showWinners(winners) {
    if (!winners || winners.length === 0) {
        console.log('No winners to display');
        isRaffleComplete = true;
        document.getElementById('startButton').disabled = false;
        return;
    }
    
    isRaffleComplete = true;
    finalResults = winners;
    
    // Display winners in boxes
    raffleBoxes.forEach((box, index) => {
        box.classList.remove('shuffling');
        box.classList.add('winner');
        
        if (winners[index]) {
            const winner = winners[index];
            box.innerHTML = `
                <div class="winner-name">${winner.name || ''}</div>
                <div class="winner-workplace">${winner.phongban || ''}</div>
                <div class="winner-company">${winner.congty || ''}</div>
            `;
        }
    });
    
    // Show fireworks and save button
    $('.pyro').html(fireworkHtml);
    showSaveBtnBlock();
    
    // Log results
    const finalIds = finalResults.map(r => r.id).join(', ');
    const finalNames = finalResults.map(r => r.name).join(', ');
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
    const availableParticipants = [...participants];
    const winners = [];

    for (let i = 0; i < count; i++) {
        if (availableParticipants.length === 0) break;
        const randomIndex = Math.floor(Math.random() * availableParticipants.length);
        winners.push(availableParticipants[randomIndex]);
        availableParticipants.splice(randomIndex, 1);
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
    if (finalResults.length === 0) return;
    
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
    if (finalResults.length === 0) return;

    const url = dataDiv.getAttribute('data-url');
    const rewardId = dataDiv.getAttribute('data-reward_id');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const clientIds = finalResults.map(winner => winner.id);

    if (predefinedResult && !clientIds.includes(predefinedResult.id)) {
        clientIds.push(predefinedResult.id);
    }

    if (assigneeId && !clientIds.includes(parseInt(assigneeId))) {
        clientIds.push(parseInt(assigneeId));
    }

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
        box.classList.remove('winner', 'shuffling');
        box.innerHTML = '<div class="shuffle-name">?</div>';
    });
}
