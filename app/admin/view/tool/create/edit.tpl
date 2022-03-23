{extend name="layouts:form"/}

{block name="content"}
<form class="form-horizontal m" id="form-edit">
    <input type="hidden" name="id" value="{$info.id}">
___REPLACE___
</form>
{/block}

{block name="script"}
<script>
    function submitHandler() {
        if ($.validate.form()) {
            $.operate.save(oesUrl, $('#form-edit').serialize());
        }
    }
</script>
{/block}
