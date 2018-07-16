<div class="crm-block crm-form-block">
  {if $smarty.get.state eq 'done'}
    <div class="help">
      {ts}Import completed Successfully.{/ts}<br/>
    </div>
  {else}
    <p>{ts}Running this will assume that the information in Constant Contact about who is
    supposed to be a in the CiviCRM group is correct.{/ts}</p>
    <p>{ts}Points to know:{/ts}</p>
    <ul>
      <li>{ts}If a contact is subscribed at Constant Contact, they will be added to the CiviCRM group (if they were in it). If the contact cannot be found in CiviCRM, a new contact will be created. {/ts}</li>
      <li>{ts}If somone's name is different, the CiviCRM name is replaced by the Constant Contact name (unless there is a name at CiviCRM but no name at Constant Contact).{/ts}</li>
      <li>{ts}Donot Mail or Donot Email will be set to Contact if email is not active in Constant Contact or any mailing(s) has recorded bounces for the email address.{/ts}</li>
    </ul>
    <table class="form-layout-compressed">
      {foreach from=$importFields item=field}
      <tr>
        <td class="label">{$form.$field.html}</td>
        <td>{$form.$field.label}</td>
      </tr>
      {/foreach}
    </table>
    <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl"}
    </div>
  {/if}
</div>
