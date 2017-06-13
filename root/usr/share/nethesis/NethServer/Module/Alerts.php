<?php
namespace NethServer\Module;

/*
 * Copyright (C) 2017 Nethesis S.r.l.
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

use Nethgui\System\PlatformInterface as Validate;

/**
 * Manage and display alerts
 *
 */
class Alerts extends \Nethgui\Controller\TableController
{

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $attributes)
    {
        return new \NethServer\Tool\CustomModuleAttributesProvider($attributes, array(
            'category' => 'Management')
        );
    }

    public function initialize()
    {
        $columns = array(
            'Type',
            'Instance',
            'Threshold'
        );

        $this
            ->setTableAdapter(new Alerts\AlertsAdapter($this->getPlatform()))
            ->setColumns($columns)
            ->addTableAction(new Alerts\Refresh())
            ->addTableAction(new Alerts\Configure())
            ->addTableAction(new \Nethgui\Controller\Table\Help('Help'))
        ;

        parent::initialize();
    }

    public function prepareViewForColumnInstance(\Nethgui\Controller\Table\Read $action, \Nethgui\View\ViewInterface $view, $key, $values, &$rowMetadata)
    {
        $instance = $values['Threshold'];
        switch ($values['Type']) {
            case "df":
               return $view->translate("Partition_label").": /".$values['Instance'];
            case "swap":
               return '-';
            case "ping_droprate":
            case "ping":
               return $view->translate("Host_label").": ".$values['Instance'];
            default:
               return $values['Instance'];
        }
    }

    public function prepareViewForColumnThreshold(\Nethgui\Controller\Table\Read $action, \Nethgui\View\ViewInterface $view, $key, $values, &$rowMetadata)
    {
        $threshold = $values['Threshold'];
        switch ($values['Type']) {
            case "df":
            case "swap":
               return $values['Threshold']." %";
            case "ping_droprate":
               return  ( $values['Threshold']*100 )." %";
            case "ping":
               return $values['Threshold']." ms";
            default:
               return $values['Threshold'];
        }
    }


    public function prepareViewForColumnType(\Nethgui\Controller\Table\Read $action, \Nethgui\View\ViewInterface $view, $key, $values, &$rowMetadata)
    {
        return $view->translate($values['Type']."_label");
    }

    # HACK: change table view (thanks to DavideP)
    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        if (isset($view['read'])) {
            $view['read']->setTemplate('NethServer\Template\Alerts');
            $stats = stat('/var/lib/nethserver/db/alerts');
            $view['read']['updated'] = date("D M j G:i:s T Y",$stats[9]);
        }
    }
}
