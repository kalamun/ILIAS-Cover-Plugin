<?php

/**
 * Class ilCoverImporter
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilCoverImporter extends ilPageComponentPluginImporter /* ilXmlImporter */
{
    /**
     * Import xml representation
     * @param string        entity
     * @param string        target release
     * @param string        id
     * @return    string        xml string
     */
    public function importXmlRepresentation(
        /* string */ $a_entity,
        /* string */ $a_id,
        /* string */ $a_xml,
        /* ilImportMapping */ $a_mapping
    ) /* : void */ {
        global $DIC;

        /** @var ilComponentFactory $component_factory */
        // $component_factory = $DIC["component.factory"]; // ILIAS 8

        /** @var ilTestPageComponentPlugin $plugin */
        /* $plugin = $component_factory->getPlugin("pCover"); // ILIAS 8 */
        $plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, 'COPage', 'pgcp', 'Cover');

        $new_id = self::getPCMapping($a_id, $a_mapping);

        $properties = self::getPCProperties($new_id);
        $version = self::getPCVersion($new_id);

        foreach(["logo", "image_1", "image_2", "image_3"] as $property) {
          $old_file_id = $properties[$property];
          if (!empty($old_file_id)) {
              $new_file_id = $a_mapping->getMapping("Modules/File", "file", $old_file_id);
              $properties[$property] = $new_file_id;
          }
        }

        self::setPCProperties($new_id, $properties);
        self::setPCVersion($new_id, $version);
    }
}