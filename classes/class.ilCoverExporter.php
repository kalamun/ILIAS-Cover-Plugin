<?php

/**
 * Class ilCoverExporter
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilCoverExporter extends ilXmlExporter
{
    public function getXmlExportHeadDependencies(/* string */ $a_entity, /* string */ $a_target_release, /* array */ $a_ids) /* : array */
    {
        $deps = [];
        foreach ($a_ids as $id) {
            $properties = ilPageComponentPluginExporter::getPCProperties($id);
            foreach(["logo", "image_1", "image_2", "image_3"] as $property) {
                $file_id = $properties[$property];
                if (!empty(($file_id))) {
                    $deps[] = array(
                        "component" => "Modules/File",
                        "entity" => "file",
                        "ids" => $file_id
                    );
                }
            }
        }

        return $deps;
    }

    /**
     * Get xml representation
     * @param string        entity
     * @param string        schema version
     * @param string        id
     * @return    string        xml string
     */
    public function getXmlRepresentation(/* string */ $a_entity, /* string */ $a_schema_version, /* string */ $a_id) /* : string */
    {
        return true;
    }

    public function init() : void
    {
        // TODO: Implement init() method.
    }

    /**
     * Get tail dependencies
     * @param string        entity
     * @param string        target release
     * @param array        ids
     * @return        array        array of array with keys "component", entity", "ids"
     */
    public function getXmlExportTailDependencies(/* string */ $a_entity, /* string */ $a_target_release, /* array */ $a_ids) /* : array */
    {
        return array();
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top. Example:
     *        return array (
     *        "4.1.0" => array(
     *            "namespace" => "http://www.ilias.de/Services/MetaData/md/4_1",
     *            "xsd_file" => "ilias_md_4_1.xsd",
     *            "min" => "4.1.0",
     *            "max" => "")
     *        );
     * @param string $a_entity
     * @return string[][]
     */
    public function getValidSchemaVersions(/* string */ $a_entity) /* : array */
    {
        return array(
            "5.2.0" => array(
                "namespace" => "http://www.ilias.de/Plugins/Cover/md/5_2",
                "xsd_file" => "ilias_md_5_2.xsd",
                "min" => "5.2.0",
                "max" => ""
            )
        );
    }
}