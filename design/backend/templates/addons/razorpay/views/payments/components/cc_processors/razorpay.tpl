{assign var="statuses" value=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses}

<div class="control-group">
    <label class="control-label cm-required" for="elm_rzp_key_id">{__("razorpay.key_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][key_id]" id="elm_rzp_key_id" value="{$processor_params.key_id}" class="input-text-large" size="40" />
        <p class="muted description">{__("razorpay.key_id_tooltip")}</p>
    </div>
</div>

<div class="control-group">
    <label class="control-label cm-required" for="elm_rzp_key_secret">{__("razorpay.key_secret")}:</label>
    <div class="controls">
        <input type="password" name="payment_data[processor_params][key_secret]" id="elm_rzp_key_secret" value="{$processor_params.key_secret}" class="input-text-large" size="40" autocomplete="new-password" />
        <p class="muted description">{__("razorpay.key_secret_tooltip")}</p>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_rzp_webhook_secret">{__("razorpay.webhook_secret")}:</label>
    <div class="controls">
        <input type="password" name="payment_data[processor_params][webhook_secret]" id="elm_rzp_webhook_secret" value="{$processor_params.webhook_secret}" class="input-text-large" size="40" autocomplete="new-password" />
        <p class="muted description">{__("razorpay.webhook_secret_tooltip")}</p>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_rzp_webhook_url">{__("razorpay.webhook_url")}:</label>
    <div class="controls">
        {assign var="webhook_url" value="payment_notification.notify?payment=razorpay"|fn_url:"C":"https"}
        <input type="text" id="elm_rzp_webhook_url" value="{$webhook_url}" class="input-text-large" size="60" readonly="readonly" onclick="this.select();" />
        <p class="muted description">{__("razorpay.webhook_url_instruction")}</p>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_rzp_payment_action">{__("razorpay.payment_action")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][payment_action]" id="elm_rzp_payment_action">
            <option value="capture"{if empty($processor_params.payment_action) || $processor_params.payment_action eq "capture"} selected="selected"{/if}>{__("razorpay.capture_immediately")}</option>
            <option value="authorize"{if $processor_params.payment_action eq "authorize"} selected="selected"{/if}>{__("razorpay.authorize_only")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_rzp_currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="elm_rzp_currency">
            <option value="store"{if empty($processor_params.currency) || $processor_params.currency eq "store"} selected="selected"{/if}>{__("razorpay.store_currency")}</option>
            <option value="INR"{if $processor_params.currency eq "INR"} selected="selected"{/if}>INR (₹ - Indian Rupee)</option>
            <option value="USD"{if $processor_params.currency eq "USD"} selected="selected"{/if}>USD ($ - US Dollar)</option>
            <option value="EUR"{if $processor_params.currency eq "EUR"} selected="selected"{/if}>EUR (€ - Euro)</option>
            <option value="GBP"{if $processor_params.currency eq "GBP"} selected="selected"{/if}>GBP (£ - British Pound)</option>
            <option value="AED"{if $processor_params.currency eq "AED"} selected="selected"{/if}>AED (UAE Dirham)</option>
            <option value="SGD"{if $processor_params.currency eq "SGD"} selected="selected"{/if}>SGD (Singapore Dollar)</option>
        </select>
        <p class="muted description">{__("razorpay.currency_tooltip")}</p>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_rzp_status_success">{__("razorpay.order_status_success")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][order_status_success]" id="elm_rzp_status_success">
            {foreach from=$statuses item="status_name" key="status_code"}
                <option value="{$status_code}"{if (!isset($processor_params.order_status_success) && $status_code eq "P") || ($processor_params.order_status_success eq $status_code)} selected="selected"{/if}>{$status_name}</option>
            {/foreach}
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_rzp_status_failed">{__("razorpay.order_status_failed")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][order_status_failed]" id="elm_rzp_status_failed">
            {foreach from=$statuses item="status_name" key="status_code"}
                <option value="{$status_code}"{if (!isset($processor_params.order_status_failed) && $status_code eq "F") || ($processor_params.order_status_failed eq $status_code)} selected="selected"{/if}>{$status_name}</option>
            {/foreach}
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_rzp_merchant_name">{__("razorpay.merchant_name")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_name]" id="elm_rzp_merchant_name" value="{$processor_params.merchant_name}" class="input-text" size="40" placeholder="{$settings.Company.company_name}" />
        <p class="muted description">{__("razorpay.merchant_name_tooltip")}</p>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_rzp_theme_color">{__("razorpay.theme_color")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][theme_color]" id="elm_rzp_theme_color" value="{if !empty($processor_params.theme_color)}{$processor_params.theme_color}{else}#3399cc{/if}" class="input-small" size="10" />
        <p class="muted description">{__("razorpay.theme_color_tooltip")}</p>
    </div>
</div>
