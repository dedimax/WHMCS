<style>
    .table td, .table th {
        border-top: none;
        vertical-align: inherit;
    }
    .bg-c-green {
        background: linear-gradient(45deg,#fff,#f5f5f5);
    }
    .float-left{
        float: left;
    }
    .float-right{
        float: right;
    }
    #domain>.row{
        display: none;
    }
</style>
<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-12 mt-12">
        <div class="card bg-c-green panel">
            <div class="card-header panel-heading">
                <!-- <span class="float-left">Server Basic Information</span> -->
                <span class="float-right">{if $status.status == 'running'}<span class="badge badge-success" style="background-color: #28a745 !important;">Running</span>{else}<span class="badge badge-danger">{$status.status|ucfirst}</span>{/if}</span>
            </div>
            <div class="card-body panel-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <td width="12%" style="text-align: left;"><b>Main IP</b></td>
                            <td width="18%" style="text-align: left">{$data.ip}</td>
                            <td width="12%" style="text-align: left;"><b>Location</b></td>
                            <td width="18%" style="text-align: left">{$data.location}</td>                            
                        </tr>
                        <tr>
                            <td width="12%" style="text-align: left;"><b>Username</b></td>
                            <td width="18%" style="text-align: left">{$params.username}</td>
                            <td width="12%" style="text-align: left;"><b>Password</b></td>
                            <td width="35%" >
                                <div class="input-group">{$params.password}</td>                            
                        </tr> 
                        <tr>
                            <td width="12%" style="text-align: left;"><b>Operating System</b></td>
                            <td width="18%" style="text-align: left">{$data.os}</td>
                            <td width="12%" style="text-align: left;" ><b>Assigned IP</b></td>
                            <td width="15%" style="text-align: left;">{$data.assignedips}</td>                            
                        </tr>                        
                    </tbody>
                </table>
                <div class="text-center">
                    <form action="" style="display: inline;" method="post">
                        <input type="hidden" name="id" value="{$serviceid}" />
                        <input type="hidden" name="customAction" value="reboot" />
                        <input type="hidden" name="modop" value="custom" />
                        <input type="hidden" name="a" value="manage" />
                        <input type="hidden" name="b" value="restart" />
                        <input type="hidden" name="s" value="{$data.id}" />
                        <button onclick="return confirm('Are You Sure?');" type="submit" class="btn btn-primary"><i class="fas fa-sync-alt"></i> Reboot</button>
                    </form>
                    {if $status.status != 'running'}
                        <form action="" style="display: inline;" method="post">
                            <input type="hidden" name="id" value="{$serviceid}" />
                            <input type="hidden" name="modop" value="custom" />
                            <input type="hidden" name="a" value="manage" />
                            <input type="hidden" name="b" value="restart" />
                            <input type="hidden" name="s" value="{$data.id}" />
                            <button class="btn btn-success"><i class="far fa-play-circle"></i> Start</button>
                        </form>
                    {/if}
                    {if $status.status == 'running'}
                        <form action="" style="display: inline;" method="post">
                            <input type="hidden" name="id" value="{$serviceid}" />
                            <input type="hidden" name="modop" value="custom" />
                            <input type="hidden" name="a" value="manage" />
                            <input type="hidden" name="b" value="stop" />
                            <input type="hidden" name="s" value="{$data.id}" />
                            <button onclick="return confirm('Are You Sure?');" class="btn btn-danger"><i class="far fa-stop-circle"></i> Stop</button>
                        </form>
                    {/if}
                    <form action="" style="display: inline;" method="post">
                        <input type="hidden" name="id" value="{$serviceid}" />
                        <input type="hidden" name="modop" value="custom" />
                        <input type="hidden" name="s" value="{$data.id}" />
                        <input type="hidden" name="a" value="manage" />
                        <input type="hidden" name="b" value="shutdown" />
                        <button onclick="return confirm('Are You Sure?');" class="btn btn-warning"><i class="fas fa-power-off"></i> Shutdown</button>
                    </form>                        
                    <button class="btn btn-primary" data-toggle="modal" data-target="#changeosm"><i class="fas fa-save"></i> Rebuild OS</button>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div id="changeosm" class="modal fade" role="dialog">
    <form method="post" style="text-align:left" action="">

        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">Select Your OS</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="{$serviceid}" />
                    <input type="hidden" name="modop" value="custom" />
                    <input type="hidden" name="s" value="{$data.id}" />
                    <input type="hidden" name="a" value="manage" />
                    <input type="hidden" name="b" value="changeos" />
                    <div class="form-group">
                        <label for="inputOsChange class="col-form-label">New OS</label>
                        <select name="osid" id="inputOsChange" class="form-control custom-select">
                            {if count($oslist)}
                                {foreach from=$oslist item=os}
                                    <option value="{$os.id}">{$os.name} {$os.version}</option>
                                {/foreach}
                            {/if}
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="text-danger">
                            <strong>Warning:</strong> This action will erase your disk and cannot be recovered!<br>
                            * This action can take up to 5 mins.
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="justify-content: space-between;">
                    <button type="submit" class="btn btn-success float-left" >Install Now</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </form>
</div>