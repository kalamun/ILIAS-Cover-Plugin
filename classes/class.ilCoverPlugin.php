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
 * Test Page Component plugin
 * @author Roberto Pasini <rp@kalamun.net>
 */
class ilCoverPlugin extends ilPageComponentPlugin
{
    /**
     * Get plugin name
     * @return string
     */
    public function getPluginName() /* : string */
    {
        return "Cover";
    }

    /**
     * Check if parent type is valid
     */
    public function isValidParentType(/* string */ $a_parent_type) /* : bool */
    {
        // test with all parent types
        return true;
    }

    /**
     * Handle an event
     * @param string $a_component
     * @param string $a_event
     * @param mixed  $a_parameter
     */
    public function handleEvent(/* string */ $a_component, /* string */ $a_event, $a_parameter) /* : void */
    {
        $_SESSION['pctpc_listened_event'] = array('time' => time(), 'event' => $a_event);
    }

    /**
     * This function is called when the page content is cloned
     * @param array  $a_properties     properties saved in the page, (should be modified if neccessary)
     * @param string $a_plugin_version plugin version of the properties
     */
    public function onClone(/* array */ &$a_properties, /* string */ $a_plugin_version) /* : void */
    {
        global $DIC;
        $mt = $DIC->ui()->mainTemplate();
        if ($file_id = $a_properties['image']) {
            try {
                include_once("./Modules/File/classes/class.ilObjFile.php");
                $fileObj = new ilObjFile($file_id, false);
                $newObj = clone($fileObj);
                $newObj->setId(0);
                $new_id = $newObj->create();
                $newObj = new ilObjFile($new_id, false);
                $this->rCopy($fileObj->getDirectory(), $newObj->getDirectory());
                $a_properties['image'] = $newObj->getId();
                $mt->setOnScreenMessage("info", "File Object $file_id cloned.", true);
            } catch (Exception $e) {
                $mt->setOnScreenMessage("failure", $e->getMessage(), true);
            }
        }
    }

    /**
     * This function is called before the page content is deleted
     * @param array  $a_properties     properties saved in the page (will be deleted afterwards)
     * @param string $a_plugin_version plugin version of the properties
     */
    public function onDelete(/* array */ $a_properties, /* string */ $a_plugin_version, /* bool */ $move_operation = false) /* : void */
    {
        global $DIC;
        $mt = $DIC->ui()->mainTemplate();

        if ($move_operation) {
            return;
        }

        if ($file_id = ($a_properties['image'] ?? null)) {
            try {
                include_once("./Modules/File/classes/class.ilObjFile.php");
                $fileObj = new ilObjFile($file_id, false);
                $fileObj->delete();
                $mt->setOnScreenMessage("info", "File Object $file_id deleted.", true);
            } catch (Exception $e) {
                $mt->setOnScreenMessage("failure", $e->getMessage(), true);
            }
        }
    }

    /**
     * Recursively copy directory (taken from php manual)
     * @param string $src
     * @param string $dst
     */
    private function rCopy(/* string */ $src, /* string */ $dst) /* : void */
    {
        $dir = opendir($src);
        if (!is_dir($dst)) {
            mkdir($dst);
        }
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->rCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }


    public function getCssFiles(/* string */ $a_mode)/* : array */
    {
        return ["css/cover.css"];
    }
    
}