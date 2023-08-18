<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Test Page Component GUI
 * @author            Roberto Pasini <rp@kalamun.net>
 * @ilCtrl_isCalledBy ilCoverPluginGUI: ilPCPluggedGUI
 * @ilCtrl_isCalledBy ilCoverPluginGUI: ilUIPluginRouterGUI
 */
class ilCoverPluginGUI extends ilPageComponentPluginGUI
{
    protected /* ilLanguage */ $lng;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;

    public function __construct()
    {
        global $DIC;

        parent::__construct();

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC['tpl'];
    }

    /**
     * Execute command
     */
    public function executeCommand() /* : void */
    {
        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            default:
                // perform valid commands
                $cmd = $this->ctrl->getCmd();
                if (in_array($cmd, array("create", "save", "edit", "update", "cancel", "downloadFile"))) {
                    $this->$cmd();
                }
                break;
        }
    }

    /**
     * Create
     */
    public function insert() /* : void */
    {
        $form = $this->initForm(true);
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Save new pc example element
     */
    public function create() /* : void */
    {
        $form = $this->initForm(true);
        if ($this->saveForm($form, true)) {
            ;
        }
        {
            $this->tpl->setOnScreenMessage("success", $this->lng->txt("msg_obj_modified"), true);
            $this->returnToParent();
        }
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    public function edit() /* : void */
    {
        $form = $this->initForm();

        $this->tpl->setContent($form->getHTML());
    }

    public function update() /* : void */
    {
        $form = $this->initForm(false);
        if ($this->saveForm($form, false)) {
            ;
        }
        {
            $this->tpl->setOnScreenMessage("success", $this->lng->txt("msg_obj_modified"), true);
            $this->returnToParent();
        }
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Init editing form
     */
    protected function initForm(bool $a_create = false) : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        // page value
        $page_value = new ilTextInputGUI('page_value', 'page_value');
        $page_value->setMaxLength(40);
        $page_value->setSize(40);
        $page_value->setRequired(false);
        $form->addItem($page_value);
        
        // page file
        $page_file = new ilFileInputGUI('page_file', 'page_file');
        $page_file->setALlowDeletion(true);
        $page_file->setRequired(true);
        $form->addItem($page_file);

        // save and cancel commands
        if ($a_create) {
            $this->addCreationButton($form);
            $form->addCommandButton("cancel", $this->lng->txt("cancel"));
            $form->setTitle($this->plugin->getPluginName());
        } else {
            $prop = $this->getProperties();
            $page_value->setValue($prop['page_value']);

            $form->addCommandButton("update", $this->lng->txt("save"));
            $form->addCommandButton("cancel", $this->lng->txt("cancel"));
            $form->setTitle($this->plugin->getPluginName());
        }

        $form->setFormAction($this->ctrl->getFormAction($this));
        return $form;
    }

    protected function saveForm(ilPropertyFormGUI $form, bool $a_create) : bool
    {
        if ($form->checkInput()) {
            $properties = $this->getProperties();

            // value saved in the page
            $properties['page_value'] = $form->getInput('page_value');

            // file object
            if (isset($_FILES["page_file"]["name"])) {
                $old_file_id = empty($properties['page_file']) ? null : $properties['page_file'];

                $fileObj = new ilObjFile((int) $old_file_id, false);
                $fileObj->setType("file");
                $fileObj->setTitle($_FILES["page_file"]["name"]);
                $fileObj->setDescription("");
                $fileObj->setFileName($_FILES["page_file"]["name"]);
                $fileObj->setMode("filelist");
                if (empty($old_file_id)) {
                    $fileObj->create();
                } else {
                    $fileObj->update();
                }
                // upload file to filesystem
                if ($_FILES["page_file"]["tmp_name"] !== "") {
                    $fileObj->getUploadFile(
                        $_FILES["page_file"]["tmp_name"],
                        $_FILES["page_file"]["name"]
                    );
                }

                $properties['page_file'] = $fileObj->getId();
            }

            if ($a_create) {
                return $this->createElement($properties);
            } else {
                return $this->updateElement($properties);
            }
        }

        return false;
    }

    /**
     * Cancel
     */
    public function cancel()
    {
        $this->returnToParent();
    }

    /**
     * Get HTML for element
     * @param string    page mode (edit, presentation, print, preview, offline)
     * @return string   html code
     */
    public function getElementHTML(/* string */ $a_mode, /* array */ $a_properties, /* string */ $a_plugin_version) /* : string */
    {
        // show uploaded file
        $image_url = false;
        $title = $a_properties['page_value'];

        if (!empty($a_properties['page_file'])) {
            try {
                $fileObj = new ilObjFile($a_properties['page_file'], false);

                // security
                $_SESSION[__CLASS__]['allowedFiles'][$fileObj->getId()] = true;

                $this->ctrl->setParameter($this, 'id', $fileObj->getId());
                $image_url = $this->ctrl->getLinkTargetByClass(array('ilUIPluginRouterGUI', 'ilCoverPluginGUI'),
                    'downloadFile');
                /* $title = $fileObj->getPresentationTitle(); */

            } catch (Exception $e) {
                /* $title = $e->getMessage(); */
            }

        }
        
        $editing = $this->ctrl->getCmd() == "edit";

        ob_start();
        ?>
        <div class="dci-cover <?= $editing ? 'is-editing' : ''; ?>">
            <?php
            if (!empty($title)) {
                ?>
                <h1><?= $title; ?></h1>
                <?php
            }
            ?>
            <?php
            if (!empty($image_url)) {
                ?>
                <img src="<?= $image_url; ?>" />
                <?php
            }
            ?>
        </div>
        <?php
        $html = ob_get_clean();
        return $html;
    }

    /**
     * download file of file lists
     */
    function downloadFile() : void
    {
        $file_id = (int) $_GET['id'];
        if ($_SESSION[__CLASS__]['allowedFiles'][$file_id]) {
            $fileObj = new ilObjFile($file_id, false);
            $fileObj->sendFile();
        } else {
            throw new ilException('not allowed');
        }
    }
}