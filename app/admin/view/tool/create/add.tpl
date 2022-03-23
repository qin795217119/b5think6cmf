{extend name="layouts:form"/}

{block name="content"}
<form class="form-horizontal m" id="form-add">
___REPLACE___
</form>
{/block}

{block name="script"}
    <script>
        function submitHandler() {
            if ($.validate.form()) {
                $.operate.save(oasUrl, $('#form-add').serialize());
            }
        }
    </script>
{/block}
