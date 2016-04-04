-- phpMyAdmin SQL Dump
-- version 2.6.1
-- http://www.phpmyadmin.net
-- 
-- ����: localhost
-- ����� ��������: ��� 16 2006 �., 15:54
-- ������ �������: 4.1.16
-- ������ PHP: 4.4.2
-- 
-- ��: `windzor`
-- 

-- --------------------------------------------------------

-- 
-- ��������� ������� `mod_job`
-- 

CREATE TABLE `mod_job` (
  `id` int(11) NOT NULL auto_increment,
  `dt` varchar(10) NOT NULL default '',
  `cat` int(11) NOT NULL default '0',
  `status` varchar(20) NOT NULL default '',
  `vac` varchar(10) NOT NULL default '',
  `age` varchar(20) NOT NULL default '',
  `visible` int(2) NOT NULL default '0',
  `move` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `dt` (`dt`,`status`,`visible`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 AUTO_INCREMENT=8 ;

-- 
-- ���� ������ ������� `mod_job`
-- 

INSERT INTO `mod_job` VALUES (1, '2006-12-12', 2, '2', '1', '', 2, 3);
INSERT INTO `mod_job` VALUES (7, '2006-12-13', 2, '2', '0', '30', 2, 1);
INSERT INTO `mod_job` VALUES (4, '2006-12-12', 1, '1', '0', '�� 40', 2, 2);

-- --------------------------------------------------------

-- 
-- ��������� ������� `mod_job_category`
-- 

CREATE TABLE `mod_job_category` (
  `id` int(4) unsigned NOT NULL auto_increment,
  `cod` int(4) unsigned NOT NULL default '0',
  `lang_id` int(4) unsigned NOT NULL default '0',
  `name` varchar(255) default NULL,
  `move` int(11) unsigned default NULL,
  `img` varchar(255) default NULL,
  `short` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `cod` (`cod`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 AUTO_INCREMENT=9 ;

-- 
-- ���� ������ ������� `mod_job_category`
-- 

INSERT INTO `mod_job_category` VALUES (1, 1, 2, '������������', 1, '', '');
INSERT INTO `mod_job_category` VALUES (2, 1, 3, '������������', 1, '', '');
INSERT INTO `mod_job_category` VALUES (3, 2, 2, 'ϳ�������', 2, '', '');
INSERT INTO `mod_job_category` VALUES (4, 2, 3, '���������', 2, '', '');
INSERT INTO `mod_job_category` VALUES (5, 3, 2, '��������', 3, '', '');
INSERT INTO `mod_job_category` VALUES (6, 3, 3, '������������', 3, '', '');
INSERT INTO `mod_job_category` VALUES (7, 4, 2, '��������', 4, '', '');
INSERT INTO `mod_job_category` VALUES (8, 4, 3, '��������', 4, '', '');

-- --------------------------------------------------------

-- 
-- ��������� ������� `mod_job_spr_contacts`
-- 

CREATE TABLE `mod_job_spr_contacts` (
  `id` int(4) unsigned NOT NULL auto_increment,
  `cod` int(4) unsigned NOT NULL default '0',
  `lang_id` int(4) unsigned NOT NULL default '0',
  `name` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `cod` (`cod`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 AUTO_INCREMENT=16 ;

-- 
-- ���� ������ ������� `mod_job_spr_contacts`
-- 

INSERT INTO `mod_job_spr_contacts` VALUES (1, 1, 2, '��������� ���������� ����, �� ���� � �� ����');
INSERT INTO `mod_job_spr_contacts` VALUES (7, 4, 2, 'ytryrtyrtytrytryrtey');
INSERT INTO `mod_job_spr_contacts` VALUES (8, 4, 3, '');
INSERT INTO `mod_job_spr_contacts` VALUES (13, 1, 3, 'yuryuruy');
INSERT INTO `mod_job_spr_contacts` VALUES (14, 7, 2, 'drththtryhrtuy');
INSERT INTO `mod_job_spr_contacts` VALUES (15, 7, 3, 'tuiuyiutiui');

-- --------------------------------------------------------

-- 
-- ��������� ������� `mod_job_spr_education`
-- 

CREATE TABLE `mod_job_spr_education` (
  `id` int(4) unsigned NOT NULL auto_increment,
  `cod` int(4) unsigned NOT NULL default '0',
  `lang_id` int(4) unsigned NOT NULL default '0',
  `name` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `cod` (`cod`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 AUTO_INCREMENT=16 ;

-- 
-- ���� ������ ������� `mod_job_spr_education`
-- 

INSERT INTO `mod_job_spr_education` VALUES (1, 1, 2, '<P>����� - Ͳ���</P>');
INSERT INTO `mod_job_spr_education` VALUES (7, 4, 2, 'wertwerrwr');
INSERT INTO `mod_job_spr_education` VALUES (8, 4, 3, '');
INSERT INTO `mod_job_spr_education` VALUES (14, 7, 2, 'iyuoiouiouoio');
INSERT INTO `mod_job_spr_education` VALUES (13, 1, 3, 'rtuy');
INSERT INTO `mod_job_spr_education` VALUES (15, 7, 3, 'itiyutiutit');

-- --------------------------------------------------------

-- 
-- ��������� ������� `mod_job_spr_experience`
-- 

CREATE TABLE `mod_job_spr_experience` (
  `id` int(4) unsigned NOT NULL auto_increment,
  `cod` int(4) unsigned NOT NULL default '0',
  `lang_id` int(4) unsigned NOT NULL default '0',
  `name` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `cod` (`cod`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 AUTO_INCREMENT=16 ;

-- 
-- ���� ������ ������� `mod_job_spr_experience`
-- 

INSERT INTO `mod_job_spr_experience` VALUES (1, 1, 2, '���²� ������ �� �����');
INSERT INTO `mod_job_spr_experience` VALUES (7, 4, 2, 'yrtyytryreyytyrtytryry');
INSERT INTO `mod_job_spr_experience` VALUES (8, 4, 3, '');
INSERT INTO `mod_job_spr_experience` VALUES (13, 1, 3, 'yyurtu');
INSERT INTO `mod_job_spr_experience` VALUES (14, 7, 2, 'ioyoyuioyituirtjy');
INSERT INTO `mod_job_spr_experience` VALUES (15, 7, 3, 'uyiutityui');

-- --------------------------------------------------------

-- 
-- ��������� ������� `mod_job_spr_position`
-- 

CREATE TABLE `mod_job_spr_position` (
  `id` int(4) unsigned NOT NULL auto_increment,
  `cod` int(4) unsigned NOT NULL default '0',
  `lang_id` int(4) unsigned NOT NULL default '0',
  `name` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `cod` (`cod`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 AUTO_INCREMENT=16 ;

-- 
-- ���� ������ ������� `mod_job_spr_position`
-- 

INSERT INTO `mod_job_spr_position` VALUES (1, 1, 2, '���-�� �������');
INSERT INTO `mod_job_spr_position` VALUES (7, 4, 2, 'ertwertwretrewt');
INSERT INTO `mod_job_spr_position` VALUES (8, 4, 3, '');
INSERT INTO `mod_job_spr_position` VALUES (13, 1, 3, 'dtdrybrybr');
INSERT INTO `mod_job_spr_position` VALUES (14, 7, 2, 'uyiyuiuy');
INSERT INTO `mod_job_spr_position` VALUES (15, 7, 3, 'gujujyuiyiyt');

-- --------------------------------------------------------

-- 
-- ��������� ������� `mod_job_spr_txt`
-- 

CREATE TABLE `mod_job_spr_txt` (
  `id` int(4) unsigned NOT NULL auto_increment,
  `cod` varchar(255) NOT NULL default '0',
  `lang_id` int(4) unsigned NOT NULL default '0',
  `name` varchar(255) default NULL,
  `move` int(11) unsigned default NULL,
  `img` varchar(255) default NULL,
  `short` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `cod` (`cod`,`lang_id`),
  KEY `move` (`move`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 AUTO_INCREMENT=67 ;

-- 
-- ���� ������ ������� `mod_job_spr_txt`
-- 

INSERT INTO `mod_job_spr_txt` VALUES (9, '_FLD_CAT', 2, '��������', 4, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (12, '_FLD_DATE', 3, '����', 5, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (11, '_FLD_DATE', 2, '����', 5, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (7, '_FID_ID', 2, '��', 3, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (8, '_FID_ID', 3, '��', 3, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (10, '_FLD_CAT', 3, '���������', 4, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (13, '_FLD_STATUS', 2, '������', 6, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (14, '_FLD_STATUS', 3, '������', 6, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (15, '_FLD_VACANCY_PROP', 2, '����������', 7, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (16, '_FLD_VACANCY_PROP', 3, '�����������', 7, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (17, '_FLD_AGE', 2, '³�', 8, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (18, '_FLD_AGE', 3, '�������', 8, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (19, '_FLD_VISIBLE', 2, '����������', 9, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (20, '_FLD_VISIBLE', 3, '����������', 9, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (21, '_FLD_MOVE', 2, '�������', 10, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (22, '_FLD_MOVE', 3, '�������', 10, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (23, '_FLD_VACANCY_NAME', 2, '�������', 11, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (24, '_FLD_VACANCY_NAME', 3, '��������', 11, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (25, '_TXT_EDIT_DATA', 2, '����������', 12, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (26, '_TXT_EDIT_DATA', 3, '�������������', 12, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (27, '_TXT_ADD_DATA', 2, '��������� �����', 13, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (28, '_TXT_ADD_DATA', 3, '���������� ������', 13, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (29, '_FLD_JOB_DISPLAY', 2, '�������', 14, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (30, '_FLD_JOB_DISPLAY', 3, '�������', 14, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (31, '_FLD_HIDDEN', 2, '�� ����������', 15, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (32, '_FLD_HIDDEN', 3, '�� ����������', 15, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (33, '_FLD_POSIRION', 2, '������', 16, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (34, '_FLD_POSIRION', 3, '���������', 16, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (35, '_FLD_EDUCATION', 2, '�����', 17, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (36, '_FLD_EDUCATION', 3, '�����������', 17, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (37, '_FLD_EXPERIENCE', 2, '����� ������', 18, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (38, '_FLD_EXPERIENCE', 3, '���� ������', 18, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (39, '_FLD_CONTACTS', 2, '��������� ����������', 19, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (40, '_FLD_CONTACTS', 3, '���������� ����������', 19, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (41, '_MSG_DATE_EMPTY', 2, '���� ���� �����', 20, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (42, '_MSG_DATE_EMPTY', 3, '���� ���� ������', 20, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (43, 'MSG_CATEGORY_EMPTY', 2, '�������, ����-�����, ��������', 21, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (44, 'MSG_CATEGORY_EMPTY', 3, '��������, ����������, ���������', 21, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (45, 'MSG_VISIBILITY_EMPTY', 2, '��������� �������� ������, ����-�����.', 22, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (46, 'MSG_VISIBILITY_EMPTY', 3, '������� ��������� ��������, ����������.', 22, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (47, 'MSG_STATUS_EMPTY', 2, '������ ������ ������, ����-�����.', 23, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (48, 'MSG_STATUS_EMPTY', 3, '������� ������ ��������, ����������.', 23, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (49, 'MSG_POSITION_EMPTY', 2, '������, ����-�����, ������', 24, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (50, 'MSG_POSITION_EMPTY', 3, '�������, ����������, ���������', 24, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (51, 'MSG_EDUCATION_EMPTY', 2, '������ ������ �� ����', 25, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (52, 'MSG_EDUCATION_EMPTY', 3, '������� ���������� �� �����������', 25, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (53, 'MSG_EXPERIENCE_EMPTY', 2, '������ ������ ���� ������ ������', 26, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (54, 'MSG_EXPERIENCE_EMPTY', 3, '������� ���������� � ����� ������', 26, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (55, 'MSG_CONTACTS_EMPTY', 2, '������ �������� ��� �����������', 27, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (56, 'MSG_CONTACTS_EMPTY', 3, '������� ���������� ����� ������������', 27, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (57, 'MSG_ERR_NOT_SAVE', 2, '������� ���������� �����', 28, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (58, 'MSG_ERR_NOT_SAVE', 3, '������ ���������� ������', 28, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (59, '_ERROR_DELETE', 2, '������� ��������� ������', 29, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (60, '_ERROR_DELETE', 3, '������ �������� ������', 29, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (61, '_TXT_JOB_TITLE', 2, '������ � ������ &quot;³�����&quot;', 30, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (62, '_TXT_JOB_TITLE', 3, '�������� � �������� &quot;�������&quot;', 30, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (63, '_TXT_JOB_DESCRIPTION', 2, '������ ³�����', 31, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (64, '_TXT_JOB_DESCRIPTION', 3, '�������� �������', 31, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (65, '_TXT_JOB_KEYWORDS', 2, '������ ³�����, ������ ������, ������, ����� ������', 32, '', '');
INSERT INTO `mod_job_spr_txt` VALUES (66, '_TXT_JOB_KEYWORDS', 3, '��������, ����� ������, ����� ������, �������� �������', 32, '', '');

-- --------------------------------------------------------

-- 
-- ��������� ������� `mod_job_statuses`
-- 

CREATE TABLE `mod_job_statuses` (
  `id` int(4) unsigned NOT NULL auto_increment,
  `cod` int(4) unsigned NOT NULL default '0',
  `lang_id` int(4) unsigned NOT NULL default '0',
  `name` varchar(255) default NULL,
  `move` int(11) unsigned default NULL,
  `img` varchar(255) default NULL,
  `short` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `cod` (`cod`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 AUTO_INCREMENT=13 ;

-- 
-- ���� ������ ������� `mod_job_statuses`
-- 

INSERT INTO `mod_job_statuses` VALUES (9, 1, 2, '��������', 1, '', '');
INSERT INTO `mod_job_statuses` VALUES (10, 1, 3, '�������', 1, '', '');
INSERT INTO `mod_job_statuses` VALUES (11, 2, 2, '������', 2, '', '');
INSERT INTO `mod_job_statuses` VALUES (12, 2, 3, '�������', 2, '', '');
        