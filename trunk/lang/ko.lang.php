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
	//$lang->msg_trash_content = '보관함에 보관되었습니다. 관리자 승인 후 등록됩니다.';
	$lang->msg_trash_content = '스팸으로 판단되어 등록되지 않았습니다. 다시 작성해 주세요.';
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
	$lang->nspam_keep = '보관';
	$lang->nspam_document_list = '문서 목록';
	$lang->nspam_keep_list = '스팸보관 목록';
	$lang->nspam_trackback_list = '엮인글 목록';
	$lang->nspam_spamdic_name = '스팸사전 명';
	$lang->nspam_spamfilter_name = '스팸필터 명';
	$lang->nspam_datetime_banned = '차단일시';
	$lang->nspam_spam_settings = '스팸대응 설정';
	$lang->nspam_deny_ip = 'IP차단';
	$lang->nspam_deny_user = '계정 사용 중지';

	$lang->cmd_delete_checked_comment = '선택댓글 삭제';
	$lang->cmd_report_as_spam_and_delete = '스팸신고 및 삭제';
	$lang->cmd_find_spamfilter = '스팸필터 찾기';
	$lang->cmd_find_spam_dictionary = '스팸사전 찾기';
	$lang->cmd_nspam_test_api = '스팸지수 확인';
	$lang->cmd_apply_spam_settings = '스팸설정 적용';
	$lang->cmd_select_spam_dictionary = '스팸사전 선택';
	$lang->cmd_select_spam_filter = '스팸필터 선택';
	$lang->cmd_delete_selected = '선택 삭제';
	$lang->cmd_restore_selected = '선택 복원';

	$lang->about_modules_to_exclude = '스팸공동대응 API 를 적용하지 않을 모듈을 선택합니다.';
	$lang->about_nspam_spamfilter = '형태소 분석, 외국어 구분 등 스팸 여부를 판정하는 소프트웨어입니다.';
	$lang->about_nspam_spam_dictionary = '단어 또는 문자열을 수록한 스팸사전입니다. 스팸사전으로 콘텐트를 검색하여 스팸 여부를 판정합니다.<br />스팸사전과 일치하는 단어가 있을 시 <span style="font-weight:bold;">무조건 스팸으로 판정</span>되며, 다른 사람이 작성한 사전을 적용할 시<br /> 정상적인 게시물도 스팸으로 분류될 수 있으므로 스팸사전을 선택할 때는 <span style="font-weight:bold;">각별히 주의해야</span> 합니다.';
	$lang->about_my_spam_dictionary = '새로운 스팸사전은 <span style="font-weight:bold;">스팸공동대응센터 홈페이지에서 네이버 아이디로 로그인 후</span> 등록하실 수 있습니다.</br>아래의 링크를 통해 스팸사전 등록 페이지로 이동하실 수 있습니다.</br><a href="http://antispam.naver.com/spamCenter/dictionary.html" style="text-decoration:underline !important; color:blue;" target="new">스팸 사전 등록</a></p>';
	$lang->about_nspam_test_api = '설정된 스팸필터와 스팸사전으로 스팸지수를 테스트 합니다.';
	$lang->about_nspam_settings = "스팸 공동대응센터에서 설정한 스팸필터와 스팸사전을 통해 확인된 '스팸 지수'가 있습니다. 스팸 지수를 이용하여 글을 어떻게 처리할 것인지 다룹니다.";
	$lang->about_nspam_settings02 = "빈 칸에는 '1 ~ 100' 사이의 숫자를 입력하세요. 숫자는 '스팸 지수(스팸일 확률)'를 의미 합니다.";
	$lang->about_nspam_settings03 = "체크된 경우에만 해당 처리를 사용합니다.";
	$lang->about_nspam_keep = '이상이면 목록에서 삭제 후 보관함 으로 이동 합니다.';
	$lang->about_nspam_deny_ip = '이상이면 글쓴이의 IP를 차단합니다.';
	$lang->about_nspam_deny_user = '이상이면 글쓴이의 계정을 사용 중지 합니다.';

	$lang->banned_search_target_list = array(
		'user_id' => '글쓴이',
		'score' => '스팸 지수(이상)',
		'title_content' => '제목 / 내용',
		'regdate' => '등록일',
	);
?>
