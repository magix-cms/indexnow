{extends file="layout.tpl"}
{block name='head:title'}indexnow{/block}
{block name='body:id'}indexnow{/block}
{block name='article:header'}
    <h1 class="h2">indexNow</h1>
{/block}
{block name='article:content'}
    {if {employee_access type="view" class_name=$cClass} eq 1}
        <div class="panels row">
            <section class="panel col-ph-12">
                {if $debug}
                    {$debug}
                {/if}
                <header class="panel-header panel-nav">
                    <h2 class="panel-heading h5">indexNow Configuration</h2>
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation"{if !$smarty.get.plugin} class="active"{/if}><a href="#general" aria-controls="general" role="tab" data-toggle="tab">{#management#}</a></li>
                        <li role="presentation"><a href="#config" aria-controls="config" role="tab" data-toggle="tab">Configuration</a></li>
                    </ul>
                </header>
                <div class="panel-body panel-body-form">
                    <div class="mc-message-container clearfix">
                        <div class="mc-message"></div>
                    </div>
                    <div class="tab-content">
                        {if !$smarty.get.plugin}
                        <div role="tabpanel" class="tab-pane active" id="general">
                            <div class="row">
                                <div class="col-ph-12">
                                    {include file="section/form/progressBar.tpl"}
                                </div>
                                <form id="send_indexnow" action="{$smarty.server.SCRIPT_NAME}?controller={$smarty.get.controller}&amp;action=push" method="post" class="form-gen col-ph-12 col-sm-6 col-md-4">
                                    <button class="btn btn-main-theme" type="submit">{#send_the_url_indexnow#|ucfirst}</button>
                                </form>
                            </div>
                            <h3>{#send_url_manually#}<a href="#" class="icon-help text-info" data-trigger="hover" data-placement="top"
                                                        data-toggle="popover"
                                                        data-content="{#info_url_manually#}"
                                                        data-original-title=""
                                                        data-title="">
                                    <span class="fa fa-question-circle"></span>
                                </a></h3>
                            <div class="row">
                                <form id="send_indexnow_text" action="{$smarty.server.SCRIPT_NAME}?controller={$smarty.get.controller}&amp;action=text" method="post" class="validate_form edit_form col-ph-12">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <div class="form-group">
                                                <label for="analyse_url">{#analyse_url#|ucfirst} *:</label>
                                                <textarea class="form-control required" id="analyse_url" name="analyse_url" cols="85" rows="10" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="btn btn-main-theme pull-right" type="submit">{#save#|ucfirst}</button>
                                </form>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="config">
                            <div class="row">
                                <form id="bridge_config" action="{$smarty.server.SCRIPT_NAME}?controller={$smarty.get.controller}&amp;action=edit" method="post" class="validate_form edit_form col-xs-12 col-md-6">
                                    <div class="row">
                                        <div class="col-xs-12 col-sm-8">
                                            <div class="form-group">
                                                <label for="apikey">Apikey :</label>
                                                <input type="text" class="form-control" id="indexnowData[apikey]" name="indexnowData[apikey]" value="{$page.apikey}" size="50" />
                                            </div>
                                        </div>
                                    </div>
                                    <div id="submit">
                                        <button class="btn btn-main-theme" type="submit" name="action" value="edit">{#save#|ucfirst}</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {/if}
                    </div>
                </div>
            </section>
        </div>
    {else}
        {include file="section/brick/viewperms.tpl"}
    {/if}
{/block}
{block name="foot" append}
    {capture name="scriptForm"}{strip}
        /{baseadmin}/min/?f=
        libjs/vendor/jquery-ui-1.12.min.js,
        libjs/vendor/progressBar.min.js,
        {baseadmin}/template/js/table-form.min.js,
        plugins/indexnow/js/indexnow.min.js
    {/strip}{/capture}
    {script src=$smarty.capture.scriptForm type="javascript"}
    <script type="text/javascript">
        $(function() {
            var controller = "{$smarty.server.SCRIPT_NAME}?controller={$smarty.get.controller}";
            if (typeof indexnow == "undefined") {
                console.log("indexnow is not defined");
            } else
            {
                indexnow.run(controller,globalForm,tableForm);
            }
        });
    </script>
{/block}