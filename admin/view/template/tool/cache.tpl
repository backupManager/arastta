<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="button" data-toggle="tooltip" title="<?php echo $button_delete; ?>" class="btn btn-danger" onclick="confirm('<?php echo $text_confirm; ?>') ? $('#form-cache').submit() : false;"><i class="fa fa-trash-o"></i></button>
                <button type="button" data-toggle="tooltip" title="<?php echo $button_deleteall; ?>" class="btn btn-danger" onclick="confirm('<?php echo $text_confirm; ?>') ? location = '<?php echo $deleteall; ?>' : false;"><i class="fa fa-times"></i></button>
            </div>
            <h1><?php echo $heading_title; ?></h1>
        </div>
    </div>
    <div class="container-fluid">
        <?php if ($error_warning) { ?>
        <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>
        <?php if ($success) { ?>
        <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $text_list; ?></h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form-cache">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <td style="width: 1px;" class="text-center"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></td>
                                <td class="text-left" style="width: 50%;">
                                    <?php echo $column_group; ?>
                                </td>
                                <td class="text-center">
                                    <?php echo $column_files; ?>
                                </td>
                                <td class="text-center">
                                    <?php echo $column_size; ?>
                                </td>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($caches) { ?>
                            <?php foreach ($caches as $group => $cache) { ?>
                            <tr>
                                <td class="text-center">
                                    <?php if (in_array($group, $selected)) { ?>
                                    <input type="checkbox" name="selected[]" value="<?php echo $group; ?>" checked="checked" />
                                    <?php } else { ?>
                                    <input type="checkbox" name="selected[]" value="<?php echo $group; ?>" />
                                    <?php } ?>
                                </td>
                                <td class="text-left">
                                    <?php echo $cache['name']; ?>
                                </td>
                                <td class="text-center">
                                    <?php echo $cache['files']; ?>
                                </td>
                                <td class="text-center">
                                    <?php echo $cache['size']; ?> KB
                                </td>
                            </tr>
                            <?php } ?>
                            <?php } else { ?>
                            <tr>
                                <td class="text-center" colspan="4"><?php echo $text_no_results; ?></td>
                            </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </form>
                <div class="row">
                    <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
                    <div class="col-sm-6 text-right"><?php echo $results; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $footer; ?>
