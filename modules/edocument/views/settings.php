<?php
/**
 * @filesource modules/edocument/views/settings.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Edocument\Settings;

use Kotchasan\Html;
use Kotchasan\Http\UploadedFile;
use Kotchasan\Language;
use Kotchasan\Text;

/**
 * module=edocument-settings
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ฟอร์มตั้งค่า
     *
     * @return string
     */
    public function render()
    {
        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/edocument/model/settings/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));
        $fieldset = $form->add('fieldset', array(
            'titleClass' => 'icon-config',
            'title' => '{LNG_Module settings}'
        ));
        // edocument_format_no
        $fieldset->add('text', array(
            'id' => 'edocument_format_no',
            'labelClass' => 'g-input icon-number',
            'itemClass' => 'item',
            'label' => '{LNG_Document No.}',
            'comment' => '{LNG_Specify the format of the document number as %04d means adding zeros until the four-digit number on the front, such as 0001.}',
            'value' => isset(self::$cfg->edocument_format_no) ? self::$cfg->edocument_format_no : 'ที่ ศธ%Y%M%D/%04d'
        ));
        $fieldset = $form->add('fieldset', array(
            'titleClass' => 'icon-upload',
            'title' => '{LNG_Upload}'
        ));
        // edocument_file_typies
        $fieldset->add('text', array(
            'id' => 'edocument_file_typies',
            'labelClass' => 'g-input icon-file',
            'itemClass' => 'item',
            'label' => '{LNG_Type of file uploads}',
            'comment' => '{LNG_Specify the file extension that allows uploading. English lowercase letters and numbers 2-4 characters to separate each type with a comma (,) and without spaces. eg zip,rar,doc,docx}',
            'value' => isset(self::$cfg->edocument_file_typies) ? implode(',', self::$cfg->edocument_file_typies) : 'doc,ppt,pptx,docx,rar,zip,jpg,pdf'
        ));
        // อ่านการตั้งค่าขนาดของไฟลอัปโหลด
        $upload_max = UploadedFile::getUploadSize(true);
        // dms_upload_size
        $sizes = array();
        foreach (array(1, 2, 4, 6, 8, 16, 32, 64, 128, 256, 512, 1024, 2048) as $i) {
            $a = $i * 1048576;
            if ($a <= $upload_max) {
                $sizes[$a] = Text::formatFileSize($a);
            }
        }
        if (!isset($sizes[$upload_max])) {
            $sizes[$upload_max] = Text::formatFileSize($upload_max);
        }
        // edocument_upload_size
        $fieldset->add('select', array(
            'id' => 'edocument_upload_size',
            'labelClass' => 'g-input icon-upload',
            'itemClass' => 'item',
            'label' => '{LNG_Size of the file upload}',
            'comment' => '{LNG_The size of the files can be uploaded. (Should not exceed the value of the Server :upload_max_filesize.)}',
            'options' => $sizes,
            'value' => isset(self::$cfg->edocument_upload_size) ? self::$cfg->edocument_upload_size : ':upload_max_filesize'
        ));
        $fieldset = $form->add('fieldset', array(
            'titleClass' => 'icon-download',
            'title' => '{LNG_Download}'
        ));
        // edocument_download_action
        $fieldset->add('select', array(
            'id' => 'edocument_download_action',
            'labelClass' => 'g-input icon-download',
            'itemClass' => 'item',
            'label' => '{LNG_When download}',
            'options' => Language::get('DOWNLOAD_ACTIONS'),
            'value' => isset(self::$cfg->edocument_download_action) ? self::$cfg->edocument_download_action : 0
        ));
        $fieldset = $form->add('fieldset', array(
            'class' => 'submit'
        ));
        // submit
        $fieldset->add('submit', array(
            'class' => 'button save large icon-save',
            'value' => '{LNG_Save}'
        ));
        \Gcms\Controller::$view->setContentsAfter(array(
            '/:upload_max_filesize/' => Text::formatFileSize($upload_max)
        ));
        // คืนค่า HTML
        return $form->render();
    }
}
