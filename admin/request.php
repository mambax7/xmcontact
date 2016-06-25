<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/**
 * xmcontact module
 *
 * @copyright       XOOPS Project (http://xoops.org)
 * @license         GNU GPL 2 (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * @author          Mage Gregory (AKA Mage)
 */
require dirname(__FILE__) . '/header.php';

// Header
xoops_cp_header();

// Get Action type
$op = system_CleanVars($_REQUEST, 'op', 'list', 'string');

switch ($op) {
    // list of request
    case 'list':
        default:
        //navigation
        $xoopsTpl->assign('navigation', $admin_class->addNavigation('request.php'));
        $xoopsTpl->assign('renderindex', $admin_class->renderIndex());

        // Get start pager
        $start = system_CleanVars($_REQUEST, 'start', 0, 'int');
        // Criteria
        $criteria = new CriteriaCompo();
        $criteria->setStart($start);
        $criteria->setLimit($nb_limit);
        // Content
        $request_count = $request_Handler->getCount($criteria);
        $request_arr = $request_Handler->getByLink($criteria);
        $xoopsTpl->assign('request_count', $request_count);
        // Define Stylesheet
        $xoTheme->addStylesheet(XOOPS_URL . '/modules/system/css/admin.css');
        // Assign Template variables
        if ($request_count > 0) {
            foreach (array_keys($request_arr) as $i) {
                $request['id'] = $request_arr[$i]->getVar('request_id');
                $request['category'] = $request_arr[$i]->getVar('category_title');
                $request['subject'] = $request_arr[$i]->getVar('request_subject');
                $request['name'] = $request_arr[$i]->getVar('request_name');
                $request['date_e'] = formatTimestamp($request_arr[$i]->getVar('request_date_e'));
                if ($request_arr[$i]->getVar('request_date_r') == 0) {
                    $request['date_r'] = '/';
                } else {
                    $request['date_r']    = formatTimestamp($request_arr[$i]->getVar('request_date_r'));
                }
                if ($request_arr[$i]->getVar('request_status') == 0){
                    $request['status'] = '<span style="color: red; font-weight:bold;">' . _AM_XMCONTACT_REQUEST_STATUS_NR . '</span>';
                } else {
                    $request['status'] = '<span style="color: green; font-weight:bold;">' . _AM_XMCONTACT_REQUEST_STATUS_R . '</span>';
                }
                $xoopsTpl->append_by_ref('request', $request);
                unset($request);
            }
            // Display Page Navigation
            if ($request_count > $nb_limit) {
                $nav = new XoopsPageNav($request_count, $nb_limit, $start, 'start');
                $xoopsTpl->assign('nav_menu', $nav->renderNav(4));
            }
        }
        break;

    // edit status
    case 'edit':
        //navigation
        $xoopsTpl->assign('navigation', $admin_class->addNavigation('request.php'));
        $xoopsTpl->assign('renderindex', $admin_class->renderIndex());
        // Define button addItemButton
        $admin_class->addItemButton(_AM_XMCONTACT_REQUEST_LIST, 'request.php', 'list');
        $xoopsTpl->assign('renderbutton', $admin_class->renderButton());

        // Create form
        $obj  = $request_Handler->get(system_CleanVars($_REQUEST, 'request_id', 0, 'int'));
        $form = $obj->getFormEdit();
        // Assign form
        $xoopsTpl->assign('form', $form->render());
        break;

    // save status
    case 'save':
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header('category.php', 3, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
        }
        $request_id = system_CleanVars($_POST, 'request_id', 0, 'int');
        if ($request_id > 0) {
            $obj = $request_Handler->get($request_id);
            if ($_POST['request_status'] == 1) {
                $obj->setVar('request_date_r', time());
            } else {
                $obj->setVar('request_date_r', 0);
            }
            $obj->setVar('request_status', $_POST['request_status']);
            if ($request_Handler->insert($obj)) {
                redirect_header('request.php', 2, _AM_XMCONTACT_REDIRECT_SAVE);
            }
            echo $obj->getHtmlErrors();
        }
        break;
}
// Call template file
$xoopsTpl->display(XOOPS_ROOT_PATH . '/modules/xmcontact/templates/admin/xmcontact_request.tpl');
xoops_cp_footer();