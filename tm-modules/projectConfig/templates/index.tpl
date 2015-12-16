<h1>Project config</h1>

<table class="table projectConfig" id="edit" name="projectConfig">
    <tr>
        <td width="20%">
            <b>Team name</b><br />
            <small>Will be displayed at the end of emails</small>
        </td>
        <td><input type="text" id="team_name" size="100" value="<?=$module->project->team_name?>" /></td>
    </tr>
    <tr>
        <td width="20%">
            <b>Challonge link</b><br />
            <small>Link to your challonge domain, for example http://<?=$module->project->name?>.challonge.com</small>
        </td>
        <td><input type="text" id="challonge_link" size="100" value="<?=$module->project->challonge_link?>" /></td>
    </tr>
    <tr>
        <td width="20%">
            <b>Challonge API key</b><br />
            <small>API key to your challonge</small>
        </td>
        <td><input type="text" id="challonge_key" size="100" value="<?=$module->project->challonge_key?>" /></td>
    </tr>
    <tr>
        <td width="20%">
            <b>Widget URL</b><br />
            <small>Page URL on which widget is located</small>
        </td>
        <td><input type="text" id="widget_url" size="100" value="<?=$module->project->widget_url?>" /></td>
    </tr>

    <tr>
        <td width="20%">
            <b>SMTP Host</b>
        </td>
        <td><input type="text" id="smtp_host" size="50" value="<?=$module->project->smtp_config->host?>" /></td>
    </tr>
    <tr>
        <td width="20%">
            <b>SMTP Login</b>
        </td>
        <td><input type="text" id="smtp_login" size="50" value="<?=$module->project->smtp_config->login?>" /></td>
    </tr>
    <tr>
        <td width="20%">
            <b>SMTP Password</b>
        </td>
        <td><input type="text" id="smtp_password" size="50" value="<?=$module->project->smtp_config->password?>" /></td>
    </tr>
    <tr>
        <td width="20%">
            <b>SMTP Port</b>
        </td>
        <td><input type="text" id="smtp_port" size="50" value="<?=$module->project->smtp_config->port?>" /></td>
    </tr>

    <tr>
        <td class="b">
            <b>Additional mail text</b><br />
            <small>Will be added at the end of email</small>
        </td>
        <td><textarea id="additional_mail_text" cols="80"><?=$module->project->additional_mail_text?></textarea></td>
    </tr>

    <? foreach($module->availableStrings as $v) { ?>
    <tr>
        <td class="b">
            <b>String - <?=$v?></b><br />
            <small>Text string for widget</small>
        </td>
        <td><textarea id="<?=$v?>" cols="80"><?=$module->strings->$v?></textarea></td>
    </tr>
    <? } ?>
    
    <tr><td colspan="2"><button class="submitButton"><?=at('edit')?> project</button></td></tr>
</table>