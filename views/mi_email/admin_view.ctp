<?php /* SVN FILE: $Id: admin_view.ctp 1763 2009-11-02 18:07:36Z AD7six $ */
extract($data);
$this->set('title_for_layout', $MiEmail['subject']);
?>
<table>
<?php
	echo $html->tableCells(array('id', $MiEmail['id']));
	echo $html->tableCells(array('from_user_id', $MiEmail['from_user_id']));
	echo $html->tableCells(array('to_user_id', $MiEmail['to_user_id']));
	echo $html->tableCells(array('chain_id', $MiEmail['chain_id']));
	echo $html->tableCells(array('send_date', $MiEmail['send_date']));
	echo $html->tableCells(array('status', $MiEmail['status']));
	echo $html->tableCells(array('type', $MiEmail['type']));
	echo $html->tableCells(array('from', $MiEmail['from']));
	echo $html->tableCells(array('to', $MiEmail['to']));
	echo $html->tableCells(array('reply_to', $MiEmail['reply_to']));
	echo $html->tableCells(array('cc', implode((array)$MiEmail['cc'], ', ')));
	echo $html->tableCells(array('bcc', implode((array)$MiEmail['bcc'], ', ')));
	echo $html->tableCells(array('send_as', $MiEmail['send_as']));
	echo $html->tableCells(array('subject', $MiEmail['subject']));
	echo $html->tableCells(array('template', $MiEmail['template']));
	echo $html->tableCells(array('layout', $MiEmail['layout']));
	echo $html->tableCells(array('data', '<pre>' . var_export($MiEmail['data'], true) . '</pre>'));
	echo $html->tableCells(array('created', $MiEmail['created']));
	echo $html->tableCells(array('modified', $MiEmail['modified']));
?>
</table>