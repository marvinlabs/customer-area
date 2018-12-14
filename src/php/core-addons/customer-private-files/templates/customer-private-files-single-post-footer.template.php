<?php
/** Template version: 3.1.1
 *
 * -= 3.1.1 =-
 * - Force download when downloading all (fixes bug with Enhanced Files addon)
 *
 * -= 3.1.0 =-
 * - Add button to download all files at once
 *
 * -= 3.0.0 =-
 * - Improve UI for new master-skin
 *
 * -= 1.3.0 =-
 * - Compatibility with the new multiple attached files
 * - New hooks for attachment items
 *
 * -= 1.2.0 =-
 * - Updated to new responsive markup
 *
 * -= 1.1.0 =-
 * - Added file size
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */
?>

<?php
global $post;
$attachments = cuar_get_the_attached_files($post->ID);
$attachment_count = count($attachments);
?>

<div class="cuar-single-post-footer">
    <div class="panel top cuar-attachments">
        <div class="panel-heading">
            <span class="panel-title">
                <?php printf(_n('%d attachment', '%d attachments', $attachment_count, 'cuar'), $attachment_count); ?>
            </span>
            <?php if (count($attachments) > 1) : ?>
                <div class="widget-menu pull-right">
                    <div class="btn btn-default btn-sm cuar-js-download-all">
                        <span class="cuar-js-btn-caption"><span class="fa fa-download"></span>&nbsp;<?php _e('Download all attachments', 'cuar'); ?></span>
                        <div class="progress cuar-js-download-progress" style="margin: 0; min-width: 140px; text-align: center; display: none;">
                            <div class="progress-bar progress-bar-default cuar-js-progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0;">
                            0%</div>
                    </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="panel-body pn">
            <table class="table table-hover table-striped">
                <tbody>
                <?php foreach ($attachments as $file_id => $file) : ?>
                    <tr>
                        <?php do_action('cuar/templates/file-attachment-item/before-caption', $post->ID, $file); ?>
                        <td class="cuar-caption">
                            <?php cuar_the_attached_file_caption($post->ID, $file); ?>
                        </td>
                        <?php do_action('cuar/templates/file-attachment-item/after-caption', $post->ID, $file); ?>
                        <td class="cuar-size"><?php cuar_the_attached_file_size($post->ID, $file); ?></td>
                        <td class="cuar-actions">
                            <a href="<?php cuar_the_attached_file_link($post->ID, $file); ?>"
                               title="<?php esc_attr_e('Get file', 'cuar'); ?>" class="btn btn-default btn-sm">
                                <span class="fa fa-download"></span>&nbsp;<?php _e('Download', 'cuar'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (count($attachments) > 1) : ?>

    <script type="application/javascript"
            src="<?php echo esc_attr(CUAR_PLUGIN_URL . '/libs/js/other/jszip/FileSaver.min.js'); ?>"></script>
    <script type="application/javascript"
            src="<?php echo esc_attr(CUAR_PLUGIN_URL . '/libs/js/other/jszip/jszip.min.js'); ?>"></script>
    <script type="application/javascript"
            src="<?php echo esc_attr(CUAR_PLUGIN_URL . '/libs/js/other/jszip/jszip-utils.min.js'); ?>"></script>
    <!--[if IE]>
    <script type="application/javascript"
            src="<?php echo esc_attr(CUAR_PLUGIN_URL . '/libs/js/other/jszip/jszip-utils-ie.min.js'); ?>"></script>
    <![endif]-->
    <script type="text/javascript">
        var Promise = window.Promise;
        if (!Promise) {
            Promise = JSZip.external.Promise;
        }

        /**
         * Fetch the content and return the associated promise.
         * @param {String} url the url of the content to fetch.
         * @return {Promise} the promise containing the data.
         */
        function urlToPromise(url)
        {
            return new Promise(function (resolve, reject) {
                JSZipUtils.getBinaryContent(url, function (err, data) {
                    if (err) {
                        reject(err);
                    } else {
                        resolve(data);
                    }
                });
            });
        }

        function updateProgress(progressElt, value) {
            progressElt.children('.cuar-js-progress-bar')
                    .attr('aria-valuenow', value)
                    .css('width', value + '%')
                    .html(value + '%');
        }

        function showProgress(captionElt, progressElt, isShown) {
            if (isShown) {
                captionElt.css('display', 'none');
                progressElt.css('display', 'block');
            } else {
                captionElt.css('display', 'block');
                progressElt.css('display', 'none');
            }
        }

        jQuery(document).ready(function ($) {
            if (!JSZip.support.blob) {
                console.log("The download all button works only with a recent browser !");
                return;
            }

            $('.cuar-js-download-all').click(function (event) {
                event.preventDefault();

                var progressElt = $(this).children('.cuar-js-download-progress');
                var captionElt = $(this).children('.cuar-js-btn-caption');

                var zip = new JSZip();

                <?php foreach ($attachments as $file_id => $file) : ?>
                zip.file(
                        '<?php echo $file['file']; ?>',
                        urlToPromise('<?php cuar_the_attached_file_link($post->ID, $file, 'download'); ?>'),
                        {binary: true});
                <?php endforeach; ?>

                // when everything has been downloaded, we can trigger the dl
                showProgress(captionElt, progressElt, true);
                updateProgress(progressElt, 0);
                zip.generateAsync(
                        {type: "blob"},
                        function updateCallback(metadata) {
                            var msg = "progression : " + metadata.percent.toFixed(2) + " %";
                            if (metadata.currentFile) {
                                msg += ", current file = " + metadata.currentFile;
                            }
                            console.log(msg);

                            updateProgress(progressElt, metadata.percent.toFixed(0));
                        })
                        .then(function callback(blob) {
                            saveAs(blob, "<?php echo sanitize_title(get_the_title()); ?>.zip");
                            showProgress(captionElt, progressElt, false);
                            updateProgress(progressElt, 0);
                        }, function (e) {
                            console.log(e);
                            showProgress(captionElt, progressElt, false);
                            updateProgress(progressElt, 0);
                        });

                return false;
            });
        });
    </script>
<?php endif; ?>
