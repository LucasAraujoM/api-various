@extends('layout.app')
@section('content')
<div class="mt-6 w-full">
    <div class="w-full flex flex-wrap gap-2 justify-center mt-2">
        <div class="w-full flex flex-col gap-2">
            <label class="text-white">Option 1: JSON Array</label>
            <textarea class="bg-gray-700 rounded px-2 py-2 text-white w-full h-40" id="emailsInput" placeholder='["test@example.com", "user@gmail.com", "invalid-email"]' oninput="updateRequestPreview()"></textarea>
        </div>
        <div class="w-full flex flex-col gap-2 mt-2">
            <label class="text-white">Option 2: Upload File (CSV/Excel)</label>
            <input type="file" id="fileInput" accept=".csv,.xlsx,.xls" class="text-white" onchange="handleFileSelect()">
        </div>
        <button class="rounded-lg px-2 py-1 bg-blue-600 text-white" onclick="testBulkAPI()">Test Bulk API</button>
        <pre class="text-xs rounded-lg px-2 py-2 w-full text-white border-1 border-gray-700" id="response"></pre>
    </div>

    <div class="flex flex-col gap-2 w-full mt-4">
        <h2>Request</h2>
        <div class="w-full flex flex-wrap gap-2 justify-center mt-2">
            <pre class="text-xs rounded-lg px-2 py-2 w-full text-white border-1 border-gray-700" id="requestPreview">{
    "emails": ["test@example.com", "user@gmail.com"]
}</pre>
        </div>
    </div>

    <div class="flex flex-col gap-2 w-full">
        <h2 class="mt-2">Endpoint</h2>
        <div class="w-full flex flex-wrap gap-2 justify-center mt-2">
            <div class="text-xs rounded-lg px-2 py-2 w-full text-white border-1 border-gray-700">
                <span class="rounded-lg px-2 py-1 bg-gray-700 text-white">{{ config('app.url') . '/api/bulk-validate-email' }}</span>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-2 w-full">
        <h2 class="mt-2">Method</h2>
        <div class="w-full flex flex-wrap gap-2 justify-center mt-2">
            <div class="text-xs rounded-lg px-2 py-2 w-full text-white border-1 border-gray-700">
                <span class="rounded-lg px-2 py-1 bg-blue-600 text-white">POST</span>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-2 w-full">
        <h2 class="mt-2">Headers</h2>
        <div class="w-full flex flex-wrap gap-2 justify-center mt-2">
            <pre class="text-xs rounded-lg px-2 py-2 w-full text-white border-1 border-gray-700">{{ json_encode([
                "Content-Type" => "multipart/form-data",
                "Authorization" => "Bearer " . (Auth::user() ? Auth::user()->getAPIKey() : 'API_KEY')
                ], JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>

    <div class="flex flex-col gap-2 w-full">
        <h2 class="mt-2">Request Body Options</h2>
        
        <h3 class="mt-2 text-green-400">Option 1: JSON Array</h3>
        <div class="w-full flex flex-wrap gap-2 justify-center mt-2">
            <pre class="text-xs rounded-lg px-2 py-2 w-full text-white border-1 border-gray-700">{{ json_encode([
                "emails" => ["test@example.com", "user@gmail.com", "invalid-email"]
                ], JSON_PRETTY_PRINT) }}</pre>
        </div>

        <h3 class="mt-2 text-green-400">Option 2: File Upload (CSV or Excel)</h3>
        <div class="w-full flex flex-wrap gap-2 justify-center mt-2">
            <pre class="text-xs rounded-lg px-2 py-2 w-full text-white border-1 border-gray-700">Content-Disposition: form-data; name="file"; filename="emails.csv"
Content-Type: text/csv

email
test@example.com
user@gmail.com
invalid-email</pre>
        </div>
    </div>

    <div class="flex flex-col gap-2 w-full">
        <h2 class="mt-2">Response (202 Accepted)</h2>
        <div class="w-full flex flex-wrap gap-2 justify-center mt-2">
            <span class="rounded-lg px-2 py-1 bg-yellow-600 text-white">202 Accepted</span>
            <pre class="text-xs rounded-lg px-2 py-2 w-full text-white border-1 border-gray-700">{{ json_encode([
                "job_id" => 1,
                "total" => 3,
                "status" => "pending",
                "message" => "Bulk validation job queued successfully."
                ], JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>

    <div class="flex flex-col gap-2 w-full">
        <h2 class="mt-4">Get Job Status</h2>
        <div class="w-full flex flex-wrap gap-2 justify-center mt-2">
            <div class="text-xs rounded-lg px-2 py-2 w-full text-white border-1 border-gray-700">
                GET <span class="rounded-lg px-2 py-1 bg-gray-700 text-white">{{ config('app.url') . '/api/bulk-jobs/{id}' }}</span>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-2 w-full">
        <h2 class="mt-2">Job Status Response (200 OK)</h2>
        <div class="w-full flex flex-wrap gap-2 justify-center mt-2">
            <pre class="text-xs rounded-lg px-2 py-2 w-full text-white border-1 border-gray-700">{{ json_encode([
                "id" => 1,
                "status" => "completed",
                "total" => 3,
                "processed" => 3,
                "results" => [
                    ["email" => "test@example.com", "valid" => true, "score" => 0.85, "mx" => true, "smtp" => true],
                    ["email" => "user@gmail.com", "valid" => true, "score" => 0.90, "mx" => true, "smtp" => true],
                    ["email" => "invalid-email", "valid" => false, "score" => 0, "error" => "invalid_syntax"]
                ]
                ], JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
</div>

<script>
    function updateRequestPreview() {
        const input = document.getElementById('emailsInput').value;
        try {
            const emails = JSON.parse(input);
            if (Array.isArray(emails)) {
                document.getElementById('requestPreview').textContent = JSON.stringify({ emails: emails }, null, 4);
            }
        } catch (e) {
            document.getElementById('requestPreview').textContent = 'Invalid JSON array';
        }
    }

    function handleFileSelect() {
        const fileInput = document.getElementById('fileInput');
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            document.getElementById('requestPreview').textContent = 'File selected: ' + file.name + ' (' + file.size + ' bytes)';
        }
    }

    function testBulkAPI() {
        const url = '{{ config('app.url') . '/api/bulk-validate-email' }}';
        const apiKey = '{{ Auth::user()->getAPIKey() }}';
        const fileInput = document.getElementById('fileInput');
        const emailsInput = document.getElementById('emailsInput').value;

        const headers = {
            'Authorization': 'Bearer ' + apiKey
        };

        if (fileInput.files.length > 0) {
            const formData = new FormData();
            formData.append('file', fileInput.files[0]);

            fetch(url, {
                method: 'POST',
                headers: headers,
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('response').textContent = JSON.stringify(data, null, 2);
                    console.log(data);
                })
                .catch(error => {
                    document.getElementById('response').textContent = JSON.stringify(error, null, 2);
                    console.error(error);
                });
        } else {
            let emails;
            try {
                emails = JSON.parse(emailsInput);
            } catch (e) {
                document.getElementById('response').textContent = 'Invalid JSON format for emails array';
                return;
            }

            const data = { emails: emails };
            headers['Content-Type'] = 'application/json';

            fetch(url, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify(data)
            })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('response').textContent = JSON.stringify(data, null, 2);
                    console.log(data);
                })
                .catch(error => {
                    document.getElementById('response').textContent = JSON.stringify(error, null, 2);
                    console.error(error);
                });
        }
    }
</script>
@endsection