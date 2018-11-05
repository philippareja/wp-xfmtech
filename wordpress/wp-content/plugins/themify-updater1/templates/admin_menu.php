<form method="post" action="">
    <h2>Themify License Settings</h2>
    <p>Enter your Themify username and license key to auto update all Themify themes and plugins. </p>
    <p>To get your license key, go to <a href="https://themify.me/member/softsale/license" target="_blank">Themify's Member Area &gt; License</a> (if you don't see your license key, <a href="https://themify.me/contact" target="_blank">contact Themify</a>). </p>
    <p>Refer to <a href="https://themify.me/docs/themify-updater-documentation" target="_blank">documentation</a> for more info.</p>
    <table>
        <tr>
            <td><strong>Themify Username</strong></td>
            <td><input type="text" value="<?php echo $username; ?>" name="themify_username" /></td>
        </tr>
        <tr>
            <td><strong>License Key</strong></td>
            <td><input type="text" value="<?php echo $key; ?>" name="updater_licence" /></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td><input type="checkbox" value="1" <?php echo $hideKey!=false ? 'checked="checked"' : ''; ?> name="hidekey" /><?php _e('Hide my license key', 'themify-updater'); ?></td>
        </tr>
    </table>
    <p><input type="submit" name="submit" id="submit" class="button button-primary" value="Save"></p>
</form>