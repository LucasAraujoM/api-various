<div class="mt-6 w-full">
    <!-- test api -->
    <div class="w-full flex flex-wrap gap-2 justify-center mt-2">
        <input class="bg-gray-700 rounded px-2 py-2 text-white w-full" type="email" id="emailInput" value="" placeholder="Enter an email address" oninput="updateRequestPreview()">
        <button class="rounded-lg px-2 py-1 bg-gray-700  text-white" onclick="testAPI()">Test API</button>
        <pre class="text-xs  rounded-lg px-2 py-2 w-full text-white border-1  border-gray-700 " id="response"></pre>
    </div>
    <div class="flex flex-col gap-2 w-full">
        <h2>Request</h2>
        <div class="w-full flex flex-wrap gap-2 justify-center mt-2">
            <pre class="text-xs  rounded-lg px-2 py-2 w-full text-white border-1  border-gray-700 " id="requestPreview">{
    "email": ""
}</pre>
        </div>
    </div>
    <div class="flex flex-col gap-2 w-full">
        <h2 class="mt-2">Endpoint</h2>
        <div class="w-full flex flex-wrap gap-2 justify-center mt-2">
            <div class="text-xs  rounded-lg px-2 py-2 w-full text-white border-1  border-gray-700 ">
                <span
                    class="rounded-lg px-2 py-1 bg-gray-700  text-white">{{ config('app.url') . '/api/email-verify' }}</span>
            </div>
        </div>
    </div>
    <div class="flex flex-col gap-2 w-full">
        <h2 class="mt-2">Method</h2>
        <div class="w-full flex flex-wrap gap-2 justify-center mt-2">
            <div class="text-xs rounded-lg px-2 py-2 w-full text-white border-1  border-gray-700 ">
                <span class="rounded-lg px-2 py-1 bg-gray-700  text-white">POST</span>
            </div>
        </div>
    </div>
    <div class="flex flex-col gap-2 w-full">
        <h2 class="mt-2">Headers</h2>
        <div class="w-full flex flex-wrap gap-2 justify-center mt-2">
            <pre class="text-xs  rounded-lg px-2 py-2 w-full text-white border-1  border-gray-700 ">{{ json_encode([
                "Content-Type" => "application/json",
                "Authorization" => "Bearer " . (Auth::user() ? Auth::user()->getAPIKey() : 'API KEY')
                ], JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
    <div class="flex flex-col gap-2 w-full">
        <h2 class="mt-2">Response</h2>
        <div class="w-full flex flex-wrap gap-2 justify-center mt-2">
            <span class="rounded-lg px-2 py-2 bg-green-700  text-white">200 OK</span>
            <pre class="text-xs  rounded-lg px-2 py-2 w-full text-white border-1  border-gray-700 ">
{{ json_encode([
                "valid" => true,
                "email" => "[EMAIL_ADDRESS]",
                "mx" => true,
                "syntax" => true,
                "smtp" => true,
                "is_alias" => false,
                "is_catch_all" => false,
                "is_disabled" => false,
                "did_you_mean" => null,
                "domain_age_days" => null,
                "is_domain_error" => null,
                "is_user_error" => null,
                "is_spam_trap" => null,
                "mailbox_level" => "unknown",
                "free" => false,
                "score" => 0.7
                ], JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
</div>

<script>
    function updateRequestPreview() {
        const email = document.getElementById('emailInput').value;
        document.getElementById('requestPreview').textContent = JSON.stringify({ email: email }, null, 4);
    }

    function testAPI() {
        const email = document.getElementById('emailInput').value;
        const url = '{{ config('app.url') . '/api/validate-email' }}';
        const apiKey = '{{ Auth::user() ? Auth::user()->getAPIKey() : 'API KEY' }}';
        const data = {
            email: email
        };
        const headers = {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + apiKey
        };
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
</script>