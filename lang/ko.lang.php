<?php
	$lang->comm = '신고 댓글 목록';
	$lang->comment_declared_list = '신고 댓글 목록';
	$lang->nspam_comment_list = '댓글 목록';
	$lang->denied_ip = '금지IP';
	$lang->document_declared_list = '신고 글 목록';
	$lang->document_trash_list = '휴지통 글 목록';
	$lang->nspam_banned_member_list = '차단회원 목록';
	$lang->nspam_banned_item_list = '스팸차단 로그';
	$lang->nspam_whether_to_use = '사용 여부';
	$lang->nspam_modules_to_exclude = '제외 모듈 선택';
	$lang->nspam_enable = '사용';
	$lang->nspam_spamfilter = '스팸필터';
	$lang->nspam_spam_dictionary = '스팸사전';
	$lang->nspam_test_api = '스팸지수 테스트';

	$lang->msg_alert_registered_denied_ip = '스팸 등록으로 차단되었습니다.';
	$lang->msg_delete_conetnet = '등록되지 않았습니다.';
	$lang->msg_spam_comment = '스팸 댓글입니다.';
	$lang->msg_trash_content = '보관함에 보관되었습니다. 관리자 승인 후 등록됩니다.';
	$lang->msg_restore_error = '복원하지 못했습니다.';
	$lang->msg_dont_use_nspam = '현재 스팸공동대응 API를 사용하지 않도록 설정되어 있습니다.';
	$lang->msg_spamapi_error = 'API 에러입니다.';
	$lang->msg_spam_comment = '스팸으로 판단되어 블라인드 처리되었습니다.';
	$lang->msg_no_spamfilter_specified = '스팸 필터가 지정되지 않았습니다. 스팸 필터를 지정해 주세요.';
	$lang->msg_no_article_selected = '글 혹은 댓글을 먼저 선택해주세요.';

	$lang->dictionary_detected = '스팸사전 일치 여부';
	$lang->dictionary_id = '스팸사전 번호';
	$lang->matched_word = '일치 단어';
	$lang->spamscore = '스팸 지수';

	$lang->nspam_config = '설정';
	$lang->nspam_document_list = '문서 목록';
	$lang->nspam_keep_list = '스팸보관 목록';
	$lang->nspam_trackback_list = '엮인글 목록';

	$lang->cmd_delete_checked_comment = '선택댓글 삭제';
	$lang->cmd_report_as_spam_and_delete = '스팸신고 및 삭제';
	$lang->cmd_find_spamfilter = '스팸필터 찾기';
	$lang->cmd_find_spam_dictionary = '스팸사전 찾기';
	$lang->cmd_nspam_test_api = '스팸지수 확인';

	$lang->about_modules_to_exclude = '스팸공동대응 API 를 적용하지 않을 모듈을 선택합니다.';
	$lang->about_nspam_spamfilter = '형태소 분석, 외국어 구분 등 스팸 여부를 판정하는 소프트웨어입니다.';
	$lang->about_nspam_spam_dictionary = '단어 또는 문자열을 담아 놓은 스팸사전입니다. 이 스팸사전으로 콘텐트를 검색하여 스팸 여부를 판정합니다.<br />스팸사전과 일치하는 단어가 있을 시 <span style="font-weight:bold;">무조건 스팸으로 판정</span>되며, 다른 사람이 작성한 스팸사전을 적용할 시<br />  정상적인 게시물도 스팸으로 분류되는 결과를 얻을 수 있으므로 스팸사전을 선택할 때는 <span style="font-weight:bold;">각별히 주의할 필요</span>가 있습니다.';
	$lang->about_my_spam_dictionary = '새로운 스팸사전은 <span style="font-weight:bold;">스팸공동대응센터 홈페이지에서 네이버 아이디로 로그인 후</span> 등록하실 수 있습니다.</br>아래의 링크를 통해 스팸사전 등록 페이지로 이동하실 수 있습니다.</br><a href="http://antispam.naver.com/spamCenter/dictionary.html" style="text-decoration:underline !important; color:blue;" target="new">스팸 사전 등록</a></p>';
	$lang->about_nspam_test_api = '설정된 스팸필터와 스팸사전으로 스팸지수를 테스트 합니다.';

	$lang->banned_search_target_list = array(
		'user_id' => '글쓴이',
		'score' => '스팸 지수(이상)',
		'title_content' => '제목 / 내용',
		'regdate' => '등록일',
	);
?>
