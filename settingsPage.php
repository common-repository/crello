<?php
if (!function_exists('add_action')) {
    echo 'Cant start directly.';
    exit;
}
?>
<div class="wrap">
    <h2>Crello Settings</h2>

    <?php if (empty($uploadDir)) { ?>
        <div style="color:red; font-size: 15px;">There are no write permissions to upload dir. Plugin will not work.</div>
    <?php } ?>

    <style>
        .form-table th.crello-row-wide {
            width:200px;
        }
        input.crello-row-wide[type="text"] {
            width:700px;
        }
        .crello-api-link {
            margin-left: 10px;
        }
    </style>

    <form method="post" action="options.php">
        <?php settings_fields('crello-settings-group'); ?>
        <table class="form-table">
            <tr>
                <th class="crello-row-wide" scope="row">Design Type</th>

                <td>
                    <select class="crello-row-wide" name="<?php echo \Crello::OPTION_DESIGN_TYPE; ?>">
                        <?php foreach (\Crello::$availableDesignTypes as $designType => $name) { ?>
                            <option
                                    value="<?php echo $designType; ?>"
                                <?php if ($designType == \Crello::getDesignType()) { echo 'selected'; } ?>
                            ><?php echo $name; ?> </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th class="crello-row-wide" scope="row">Api Key</th>
                <td>
                    <input
                            class="crello-row-wide"
                            type="text"
                            name="<?php echo \Crello::OPTION_API_KEY; ?>"
                            value="<?php echo get_option(\Crello::OPTION_API_KEY); ?>"
                    />
                    <a class="crello-api-link" href="https://crello.com/crello-button/documentation/" target="_blank">Request an API key &#187;</a>
                </td>
            </tr>
        </table>

        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>

    </form>
</div>
