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
        $title = new ilTextInputGUI($this->lng->txt("title"), 'title');
        $title->setMaxLength(40);
        $title->setSize(40);
        $title->setRequired(false);
        $form->addItem($title);
        
        // page value
        $alignment = new ilSelectInputGUI($this->lng->txt("alignment"), 'alignment');
        $alignment->setPostVar("alignment");
        $alignment->setOptions(["left" => "Left", "center" => "Center", "right" => "Right"]);
        $alignment->setRequired(false);
        $form->addItem($alignment);
        
        // logo
        $logo = new ilFileInputGUI($this->lng->txt("logo"), 'logo');
        $logo->setALlowDeletion(true);
        $logo->setRequired(false);
        $form->addItem($logo);

        // page file
        $image_1 = new ilFileInputGUI($this->lng->txt("image"), 'image_1');
        $image_1->setALlowDeletion(true);
        $image_1->setRequired(false);
        $form->addItem($image_1);

        $image_2 = new ilFileInputGUI($this->lng->txt("image"), 'image_2');
        $image_2->setALlowDeletion(true);
        $image_2->setRequired(false);
        $form->addItem($image_2);

        $image_3 = new ilFileInputGUI($this->lng->txt("image"), 'image_3');
        $image_3->setALlowDeletion(true);
        $image_3->setRequired(false);
        $form->addItem($image_3);

        // save and cancel commands
        if ($a_create) {
            $this->addCreationButton($form);
            $form->addCommandButton("cancel", $this->lng->txt("cancel"));
            $form->setTitle($this->plugin->getPluginName());
        } else {
            $prop = $this->getProperties();
            $title->setValue($prop['title']);
            $alignment->setValue($prop['alignment']);

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
            $properties['title'] = $form->getInput('title');
            $properties['alignment'] = $form->getInput('alignment');
            
            // file object
            foreach(["logo", "image_1", "image_2", "image_3"] as $key) {
                if (isset($_FILES[$key]["name"])) {
                    $old_file_id = empty($properties[$key]) ? null : $properties[$key];
                    
                    $fileObj = new ilObjFile((int) $old_file_id, false);
                    $fileObj->setType("file");
                    $fileObj->setTitle($_FILES[$key]["name"]);
                    $fileObj->setDescription("");
                    $fileObj->setFileName($_FILES[$key]["name"]);
                    $fileObj->setMode("filelist");
                    if (empty($old_file_id)) {
                        $fileObj->create();
                    } else {
                        $fileObj->update();
                    }
    
                    // upload file to filesystem
                    if ($_FILES[$key]["tmp_name"] !== "") {
                        $fileObj->getUploadFile(
                            $_FILES[$key]["tmp_name"],
                            $_FILES[$key]["name"]
                        );
                    }
    
                    $properties[$key] = $fileObj->getId();
                }
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
        $title = $a_properties['title'];
        $alignment = $a_properties['alignment'];
        $image_url = [];
        
        // file object
        foreach(["logo", "image_1", "image_2", "image_3"] as $key) {
            if (!empty($a_properties[$key])) {
                try {
                    $fileObj = new ilObjFile($a_properties[$key], false);
                    
                    // security
                    $_SESSION[__CLASS__]['allowedFiles'][$fileObj->getId()] = true;
                    
                    $this->ctrl->setParameter($this, 'id', $fileObj->getId());
                    $image_url[$key] = $this->ctrl->getLinkTargetByClass(array('ilUIPluginRouterGUI', 'ilCoverPluginGUI'),
                    'downloadFile');
                    /* $title = $fileObj->getPresentationTitle(); */
                    
                } catch (Exception $e) {
                    /* $title = $e->getMessage(); */
                }
            }
        }
        
        include_once "Services/Style/System/classes/class.ilStyleDefinition.php";
        $dci_skin = ilStyleDefinition::getCurrentSkin() === 'dci';
        
        ob_start();
        ?>
        <div class="dci-cover <?= !$dci_skin ? 'is-editing' : ''; ?> align-<?= $alignment; ?>">
            <?php
            if (!empty($image_url["logo"])) {
                ?>
                <img src="<?= $image_url["logo"]; ?>" class="logo" title="<?= $title; ?>" />
                <?php
            }
            ?>

            <div class="carousel">
                <div class="splide">
                    <div class="splide__track">
                        <ul class="splide__list">
                        <?php
                        foreach(["image_1", "image_2", "image_3"] as $key) {
                            if (!empty($image_url[$key])) {
                                ?>
                                <li class="splide__slide">
                                    <img src="<?= $image_url[$key]; ?>" />
                                </li>
                                <?php
                            }
                        }
                        ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <script>
        document.addEventListener( 'DOMContentLoaded', function() {
            new Splide( '.splide', {
                type   : 'loop',
                perPage: 1,
                autoplay: 'play',
            }).mount();
        } );
        </script>
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