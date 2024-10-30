<div class="ioptimize-wrap">
    <img src="<?= plugins_url('../img/logo-i.svg', __FILE__) ?>" class="ioptimizer-logo" />
	   <?php settings_errors(); ?>
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab-1">Account</a></li>
        <li><a href="#tab-2">Images</a></li>
    </ul>
    <div class="tab-content">
        <div id="tab-1" class="tab-pane active ioptimize-main-panel">
          <div class="ioptimize-config-area">
            <form method="post" action="options.php">
    				<?php
    				settings_fields( 'ioptimizer_auth_group' );
    				do_settings_sections( 'ioptimizer' );
    				?>
                <table>
                    <tr>
                        <th scope="row"><label for="ioptimizer_tokens">Credits</label></th>
                        <td>
                            <p class="tokens-no">---</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ioptimizer_status">Automatic processing</label></th>
                        <td>
                            <input type="checkbox" id="ioptimizer_status" name="ioptimizer_status" <?php if ( get_option( 'ioptimizer_status' ) == 'on' ): ?> checked <?php endif; ?> />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ioptimizer_lazy_load">Lazy load images </label></th>
                        <td>
                            <input type="checkbox" id="ioptimizer_lazy_load" name="ioptimizer_lazy_load" <?php if ( get_option( 'ioptimizer_lazy_load' ) == 'on' ): ?> checked	<?php endif; ?> />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ioptimizer_token">Authentification token</label></th>
                        <td>
                            <input type="text" id="ioptimizer_token" name="ioptimizer_token" value="<?= get_option( 'ioptimizer_token' ); ?>"/>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                </p>
            </form>
          </div>
          <div class="ioptimize-promotion-area">
            <div class="promotion-area-container">
              <h2>GET MORE CREDITS</h2>
              <p>Visit us on <a href="#">I-Optimizer</a> to get more credits and support our work.</p>
              <a href="<?=get_option('ioptimizer_host')?>?host-name=<?=get_option( 'siteurl' )?>&platform=wordpress#pricing"class="button button-primary buy-tokens-btn">Top up credits</a>
            </div>
          </div>
        </div>
        <div id="tab-2" class="tab-pane ">
          <div class="ioptimize-column-left">
            <h2>Image Optimization</h2>
            <span class="tokens-needed"></span>
            <div class="ioptimize-image-filter-container">

            </div>
            <div class="ioptimize-image-list-container">

              <div class="tbl-header tbl-ioptimizer-header-images">
                <table cellpadding="0" cellspacing="0" border="0">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Original size</th>
                        <th>Optimized size</th>
                        <th>Reduction %</th>
                        <th>Thumb</th>
                    </tr>
                    </thead>
                </table>
              </div>
              <div class="tbl-content tbl-ioptimizer-images">
                <table class="images-list" cellpadding="0" cellspacing="0" border="0">
                    <tbody>
                    </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="ioptimize-column-right">
            <div class="sidebar-container">
              <h2>Bulk Optimization</h2>
              <p>Select all the images that need optimization</p>
                <input type="button" class="select-all button button-primary" disabled value="Select all">
                <input type="button" class="bulk-process button button-primary" disabled value="Process selected">
            </div>
          </div>
        </div>


    </div>
</div>
