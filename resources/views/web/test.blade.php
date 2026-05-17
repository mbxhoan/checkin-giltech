<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Client Storage Benchmark</title>
    <style>
        #layout {
            display: flex;
            gap: 20px;
        }

        #logs {
            width: 40%;
            height: 500px;
            overflow-y: auto;
            background: #111;
            color: #0f0;
            padding: 10px;
            font-family: monospace;
            border-radius: 6px;
        }

        #table-container {
            width: 60%;
            height: 500px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 6px;
            background: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
        }

        table thead {
            background: #eee;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            font-size: 13px;
            text-align: left;
        }
    </style>
</head>

<body>
    <h2>Client Storage Benchmark</h2>

    <button onclick="runAllTests()">Run Benchmark</button>
    <pre id="output"></pre>

    <div id="layout">
        <div id="table-container">
            <table id="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>UUID</th>
                        <th>Email</th>
                        <th>Phone</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <script>
        // function log(msg) {
        //     const div = document.getElementById("logs");
        //     div.innerText += msg + "\n";
        //     div.scrollTop = div.scrollHeight;
        // }

        // Mock data $clients (10,000 records)
        let clients = Array.from({
            length: 1000000
        }, (_, i) => ({
            id: i + 1,
            uuid: crypto.randomUUID(),
            name: "Client " + (i + 1),
            email: `client${i}@mail.com`,
            phone: "0123456789"
        }));

        const output = document.getElementById("output");
        const log = msg => output.textContent += msg + "\n";

        // ----------------------- 1. localStorage -----------------------
        async function testLocalStorage() {
            log("\n=== localStorage (with limit detection) ===");

            // clear old data
            localStorage.removeItem("clients");

            let chunkSize = 1000; // write in chunks to avoid huge JSON string writes
            let total = clients.length;
            let saved = 0;

            try {
                while (saved < total) {
                    let slice = clients.slice(0, saved + chunkSize);
                    let json = JSON.stringify(slice);

                    localStorage.setItem("clients", json);
                    saved += chunkSize;

                    // log(`Saved ${saved} records...`);
                }

                log("\nLocalStorage saved all records successfully.");
            } catch (err) {
                if (err.name === "QuotaExceededError") {
                    log("\n❌ QuotaExceededError reached!");
                    log(`Maximum storable records: ${saved.toLocaleString()}`);
                    log(`Estimated size: ${(localStorage.getItem("clients").length / (1024 * 1024)).toFixed(3)} MB`);
                } else {
                    log("\nUnexpected error: " + err);
                }
            }

            // Read test (if any data)
            try {
                let t1 = performance.now();
                let data = JSON.parse(localStorage.getItem("clients"));
                let t2 = performance.now();

                log(`Read time: ${(t2 - t1).toFixed(2)} ms`);
                log(`Actual stored count: ${data.length.toLocaleString()}`);
            } catch {
                log("Could not read data (probably cache empty).");
            }
        }


        // ----------------------- 2. IndexedDB -----------------------
        function openDB() {
            return new Promise((resolve, reject) => {
                let req = indexedDB.open("ClientDB", 1);
                req.onupgradeneeded = e => {
                    let db = e.target.result;
                    db.createObjectStore("clients", {
                        keyPath: "id"
                    });
                };
                req.onsuccess = e => resolve(e.target.result);
                req.onerror = reject;
            });
        }

        async function testIndexedDB() {
            log("\n=== IndexedDB ===");

            const db = await openDB();

            let t1 = performance.now();
            let tx = db.transaction("clients", "readwrite");
            let store = tx.objectStore("clients");
            for (let item of clients) store.put(item);
            await tx.done;
            let t2 = performance.now();

            let t3 = performance.now();
            let tx2 = db.transaction("clients", "readonly");
            let getAllReq = tx2.objectStore("clients").getAll();
            let data = await new Promise(res => {
                getAllReq.onsuccess = () => res(getAllReq.result);
            });
            let t4 = performance.now();

            log("Write time: " + (t2 - t1).toFixed(2) + "ms");
            log("Read time: " + (t4 - t3).toFixed(2) + "ms");
            log("Count: " + data.length.toLocaleString());
        }

        // ----------------------- 3. Cache Storage API -----------------------
        async function testCacheStorage() {
            log("\n=== Cache Storage API ===");

            const cache = await caches.open("client-cache");
            const blob = new Blob([JSON.stringify(clients)], {
                type: "application/json"
            });

            let t1 = performance.now();
            await cache.put("/clients.json", new Response(blob));
            let t2 = performance.now();

            let t3 = performance.now();
            let response = await cache.match("/clients.json");
            let data = await response.json();
            let t4 = performance.now();

            log("Write time: " + (t2 - t1).toFixed(2) + "ms");
            log("Read time: " + (t4 - t3).toFixed(2) + "ms");
            log("Size: " + (blob.size / 1024 / 1024).toFixed(3) + " MB");
        }

        // ----------------------- 4. Filesystem Access API (PWA) -----------------------
        async function testFilesystemAPI() {
            if (!window.showSaveFilePicker) {
                log("\n❌ Filesystem Access API not supported on this browser");
                return;
            }

            log("\n=== Filesystem Access API ===");

            const json = JSON.stringify(clients);

            try {
                const handle = await showSaveFilePicker({
                    suggestedName: "clients.json",
                    types: [{
                        description: "JSON",
                        accept: {
                            "application/json": [".json"]
                        }
                    }]
                });

                let writable = await handle.createWritable();

                let t1 = performance.now();
                await writable.write(json);
                await writable.close();
                let t2 = performance.now();

                log("Write time: " + (t2 - t1).toFixed(2) + "ms");
                log("Saved file size: " + (json.length / 1024 / 1024).toFixed(3) + " MB");
            } catch (err) {
                log("\nUnexpected error: " + err);
                log(err.name);
                return;
            }
        }

        // ----------------------- Run All -----------------------
        async function runAllTests() {
            output.textContent = "";
            log(`Total data: ${clients.length.toLocaleString()}`);
            await testLocalStorage();
            await testIndexedDB();
            await testCacheStorage();
            await testFilesystemAPI();
            log("\n=== DONE ===");
        }

        // --- Render table with max 100 rows ---
        function renderTable(maxRows = 100) {
            const tbody = document.querySelector("#data-table tbody");
            tbody.innerHTML = "";

            clients.slice(0, maxRows).forEach((c, i) => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${i + 1}</td>
                    <td>${c.uuid}</td>
                    <td>${c.email}</td>
                    <td>${c.phone}</td>
                `;
                tbody.appendChild(tr);
            });

            log(`Rendered table with ${maxRows.toLocaleString()} rows`);
        }

        // Render table on page load
        renderTable(100);
    </script>
</body>

</html>
