<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">MeshCentral Management</h3>
    </div>
    <div class="card-body">

        {if $error}
            <div class="alert alert-danger">
                <p>{$error}</p>
            </div>
        {else}
            <p>Use the links below to manage your connected devices.</p>

            <a href="{$ssoLink}" target="_blank" class="btn btn-primary mb-3">
                <i class="fas fa-sign-in-alt"></i> Access Control Panel
            </a>
            
            <hr>

            <h4><i class="fas fa-download"></i> Install Agent on a New PC</h4>
            <p>To add a computer to your management panel, download and run this installer on it.</p>
            <div class="input-group mb-3">
                <input type="text" class="form-control" value="{$agentLink}" readonly id="agentLinkInput">
                <div class="input-group-append">
                    <button class="btn btn-secondary" onclick="copyToClipboard('#agentLinkInput')">Copy</button>
                </div>
            </div>
            <a href="{$agentLink}" class="btn btn-success">Download Windows Agent</a>

            <hr>
            
            <h4><i class="fas fa-desktop"></i> Your Devices</h4>
            {if $devices}
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Hostname</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$devices item=device}
                                <tr>
                                    <td>{$device.name}</td>
                                    <td>
                                        {* Connection state: 0=offline, 1=agent, 2=cira, 3=amt, 4=relay *}
                                        {if $device.conn > 0}
                                            <span class="badge badge-success">Online</span>
                                        {else}
                                            <span class="badge badge-danger">Offline</span>
                                        {/if}
                                    </td>
                                    <td class="text-center">
                                        <a href="{$meshServerUrl}/?login={$device._id}" target="_blank" class="btn btn-sm btn-info {if $device.conn == 0}disabled{/if}">
                                            <i class="fas fa-plug"></i> Connect
                                        </a>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            {else}
                <div class="alert alert-info text-center">
                    No devices have been connected yet. Use the installer link above to add your first device.
                </div>
            {/if}
        {/if}
    </div>
</div>

<script type="text/javascript">
function copyToClipboard(element) {
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val($(element).val()).select();
    document.execCommand("copy");
    $temp.remove();
    alert("Agent link copied to clipboard!");
}
</script>