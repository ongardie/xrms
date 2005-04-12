<?php
/** 
 *   This file provides the textual interface of WikkiTikkiTavi
 * For more information see http://tavi.sourceforge.net/TaviTranslation
 *
 *   This file is divided into the sections according to subdirectories of 
 * where the constants are used. So that the constant PARSE_RecentChanges are
 * to be found somewhere within the parse-subdirectory.
 *
 *   See http://tavi.sourceforge.net/TaviTranslation for notes on how to 
 * translate the file into another language, and how to publish your changes.
 * Please do contribute to make 'Tavi available in multiple languages. 
 *   The gist of translation, is to copy this file and translate it. Store the
 * resulting work as lang_XX.php, where XX denotes the two characters used to
 * describe the language. And after that to add a line like the following to
 * your config.php:
 *   setConst('LANGUAGE_CODE', 'zh_cn');
 * Using 'zh_cn' for the Simplified Chinese version, if available.
 */

/* General messages */
setConst('GEN_ErrorSuffix', 
       '<br />请�?�系 <a href="mailto:' . 
       $Admin . '">管�?�员</a> 寻求技术支�?。');

/* action-directory */
setConst('ACTION_RateControlIpBlocking', '刷新频率控制 / IP 地�?��?�?');
setConst('ACTION_LockUnlockPages', '�?定 / 释放页�?�');
setConst('ACTION_BLockUnblockHosts', '�?�? / 解�?主机地�?�');
setConst('ACTION_ErrorNameMatch',
       '� 输入了� 效的用户�??。' . GEN_ErrorSuffix);
setConst('ACTION_ErrorAdminDisabled',
       'WIKI管�?�功能已关�。'.GEN_ErrorSuffix);
setConst('ACTION_ErrorPageLocked',
       '� �?试编辑的页�?�已�?被管�?�员�?定。' . GEN_ErrorSuffix);

/* lib-directory */
setConst('LIB_ErrorDatabaseQuery', 
       '数�?�库查询执行错误。' . GEN_ErrorSuffix);
setConst('LIB_ErrorDatabaseConnect', 
       '连接数�?�库失败。' . GEN_ErrorSuffix); 
setConst('LIB_ErrorDatabaseSelect', 
       '数�?�库选择错误。' . GEN_ErrorSuffix); 
setConst('LIB_ErrorCreatingTemp', 
       '缓�文件创建错误。' . GEN_ErrorSuffix);
setConst('LIB_ErrorWritingTemp',
       '写入缓�文件错误。' . GEN_ErrorSuffix);
// The next two entries are parsed through the wiki engine, so leave the \'\'
// to get bolded entries... And the escaping of $!DiffCmd...   ;)
setConst('LIB_NoDiffAvailableSafeMode',
       '\'\'没有�?�用的比较工具diff，�?�能是 diffcmd 放置在错误的�?置。'.
       '应放置在 '. ini_get("safe_mode_exec_dir") . ' 內，'.
       '但其�?置在 '. $DiffCmd .'。请�?新放置 diffcmd 或'.
       '请系统管�?�员创建一个符�?�连接到该目录。也请'.
       '记得将设定设定修改到与 $!DiffCmd 一致的�?置。\'\'');       
setConst('LIB_NoDiffAvailable',
       '\'\'没有�?�用的比较工具diff。�?于' . $DiffCmd . ' 的 ' .'$!DiffCmd'.
       '�?�在或� 法读�?�\'\'');
setConst('LIB_ErrorDeniedAccess',
       '本站拒�?� 的��?��?作。' . GEN_ErrorSuffix);
setConst('LIB_ErrorRateExceeded',
       '您的刷新速度过快，请�?�?��?�?试。' . GEN_ErrorSuffix);
setConst('LIB_ErrorInvalidPage',
       '� 效的页�?��??称。' . GEN_ErrorSuffix);

/* parse-directory */
setConst('PARSE_Added', '已新增: ');
setConst('PARSE_Changed', '已修改: ');
setConst('PARSE_Deleted', '已� 除: ');
setConst('PARSE_Never', '从未'); 
// The next two entries, are joined with a counting variable inbetween
setConst('PARSE_CompleteListStart', '查看完整清�?� (');
setConst('PARSE_CompleteListEnd',   ' )');
setConst('PARSE_RecentChanges', '近期更新');
setConst('PARSE_Locked', '已�?定');
setConst('PARSE_BlockedRange', '被�?�?的 IP 地�?�范围');
setConst('PARSE_EnterIpRange', 
       '以�� ��? <tt>12.*</tt>, <tt>34.56.*</tt>，或 ' .
       '<tt>78.90.123.*</tt> 输入 IP 地�?�');
setConst('PARSE_EmptyToBeDeleted',
       ' ** 空的, 预定刪除 ** ');
setConst('PARSE_From', '�?�自'); //ie. SomeThing *from* WantedPages, MacroSomePage
setConst('PARSE_ButtonBlock', ' �?  �? ');
setConst('PARSE_ButtonUnblock', ' 解  �? ');
setConst('PARSE_ButtonSave', ' �?  � ');
setConst('PARSE_ButtonPreview', ' 预  览 ');
 
/* Template directory */
// Note the change to use only TMPL_ as prefix instead of full TEMPLATE_
setConst('TMPL_Administration', 'WIKI管�?�页�?�');
setConst('TMPL_EditDocument', '编辑该页�?�');
setConst('TMPL_NoEditDocument', '该页�?�� 法编辑');
setConst('TMPL_EditArchiveVersion', 
       '编辑该页�?�的<em>历�?�记录版本</em>');
// Next entry is followed by a date as produced by html_time()
setConst('TMPL_ViewDocHistory', '�?览该页�?�的历�?�记录');
setConst('TMPL_DocLastModified', '文件最�?�一次修改于');
setConst('TMPL_TwinPages', '�?�胞胎页�?�:');
setConst('TMPL_Search', '�?�索:');
setConst('TMPL_Editing', '�在编辑');
setConst('TMPL_WarningOtherEditing',
       '�告: 在您开始编辑之�?�，本页�?�已被其他人修改过。'.
       '请将� 的修改�?�并到当�?最新版本之�。');
setConst('TMPL_CurrentVersion', '当�?的版本');
setConst('TMPL_ButtonSave', ' �?�修改 ');
setConst('TMPL_ButtonPreview', ' 预  览 ');
setConst('TMPL_YourUsername', '您的用户�??是');
setConst('TMPL_VisitPrefs', 
       '请进入<a href="'. $PrefsScript. '">�??好设定页�?�</a>�?�修改'.
       '您的用户�??');
setConst('TMPL_SummaryOfChange', '本次修改的简�?说明:');
setConst('TMPL_AddToCategory', '将该页�?�归入该类别:');
setConst('TMPL_YourChanges', '您的修改');
setConst('TMPL_PreviewCurrentVersion', '预览当�?的版本');
setConst('TMPL_DifferencesIn', '差异：');
setConst('TMPL_DifferenceBetweenVersions', '版本间的�?�?�之处');
setConst('TMPL_Find', '寻找');
setConst('TMPL_HistoryOf', '历�?�记录:');
setConst('TMPL_Older', '较旧的');
setConst('TMPL_Newer', '较新的');
setConst('TMPL_ButtonComputeDifference', '对比�?�?�点');
setConst('TMPL_ChangesLastAuthor', '最近一�?作者的修改:');
setConst('TMPL_Prefs', '�??好设定');
setConst('TMPL_Previewing', '�在预览');
setConst('TMPL_Preview', '预览');

?>