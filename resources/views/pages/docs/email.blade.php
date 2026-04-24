<div class="mt-6 w-full">
    <div class="flex flex-col gap-2 w-full">
        <h2>Solicitud</h2>
        <div class="w-full flex flex-wrap gap-2 justify-center mt-2">
            <pre class="text-xs  rounded-lg px-2 py-1 w-full text-white border-1  border-gray-700 ">{{ json_encode([
                "email" => "[EMAIL_ADDRESS]"
                ], JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
    <div class="flex flex-col gap-2 w-full">
        <h2 class="mt-2">Endpoint</h2>
        <div class="w-full flex flex-wrap gap-2 justify-center mt-2">
            <div class="text-xs  rounded-lg px-2 py-1 w-full text-white border-1  border-gray-700 ">
                <span
                    class="rounded-lg px-2 py-1 bg-gray-700  text-white">{{ config('app.url') . '/api/email-verify' }}</span>
            </div>
        </div>
    </div>
    <div class="flex flex-col gap-2 w-full">
        <h2 class="mt-2">Método</h2>
        <div class="w-full flex flex-wrap gap-2 justify-center mt-2">
            <div class="text-xs  rounded-lg px-2 py-1 w-full text-white border-1  border-gray-700 ">
                <span class="rounded-lg px-2 py-1 bg-gray-700  text-white">POST</span>
            </div>
        </div>
    </div>
    <div class="flex flex-col gap-2 w-full">
        <h2 class="mt-2">Headers</h2>
        <div class="w-full flex flex-wrap gap-2 justify-center mt-2">
            <pre class="text-xs  rounded-lg px-2 py-1 w-full text-white border-1  border-gray-700 ">{{ json_encode([
                "Content-Type" => "application/json",
                "Authorization" => "Bearer " . (Auth::user() ? Auth::user()->getAPIKey() : 'API KEY')
                ], JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
    <div class="flex flex-col gap-2 w-full">
        <h2 class="mt-2">Respuestas</h2>
        <div class="w-full flex flex-wrap gap-2 justify-center mt-2">
            <span>200 OK</span>
            <pre class="text-xs  rounded-lg px-2 py-1 w-full text-white border-1  border-gray-700 ">
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