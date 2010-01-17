<?php /* SVN FILE: $Id: admin_index.ctp 1962 2009-12-01 20:59:48Z ad7six $ */ ?>
<table class="stickyHeader">
<?php
$this->set('title_for_layout', __('Emails', true));
$paginator->options(array('url' => $this->passedArgs));
echo '<thead><tr><th>' . $paginator->sort('id') .
	'</th><th colspan=2>' . $paginator->sort('subject') .
	'</th><th>' . $paginator->sort('created') .
	'</th></tr>';
echo '<tr><th>&nbsp;' .
	'</th><th>' . $paginator->sort('from_user_id') .
	'</th><th>' . $paginator->sort('to_user_id') .
	'</th><th>' . $paginator->sort('status') .
	'</th></tr></thead>';
foreach ($data as $i => $row) {
	extract($row);
	if (empty($users[$FromUser['id']])) {
		$fromName = trim(htmlspecialchars(preg_replace('@<.*>@', '', $MiEmail['from'])));
	} else {
		$fromName = $users[$FromUser['id']];
	}
	$from = $html->link($fromName, 'mailto:' . $MiEmail['from'], array('target' => '_blank'));
	if (empty($users[$ToUser['id']])) {
		$toName = trim(htmlspecialchars(preg_replace('@<.*>@', '', $MiEmail['to'])));
	} else {
		$toName = $users[$ToUser['id']];
	}
	$to = $html->link($toName, 'mailto:' . $MiEmail['to'], array('target' => '_blank'));
	$tr = array(
		array(
			$html->link($MiEmail['id'], array('admin' => true, 'action' => 'view', $MiEmail['id']), array('title' => 'Web view')),
			array($html->Link($MiEmail['subject'], array('action' => 'text_preview', $MiEmail['id']), array('class' => 'popup', 'title' => 'popup preview (text format)')), array('colspan' => 2)),
			$time->niceShort($MiEmail['created']),
		),
		array(
			$html->link('# ', array('admin' => false, 'action' => 'view', $MiEmail['id']), array('style' => 'display:none;')),
			$from,
			$to,
			$MiEmail['status'],
		)
	);

	$class = $i%2?'even':'odd';
	echo $html->tableCells($tr, compact('class'), compact('class'));
}
?>
</table>
<?php echo $this->element('paging');