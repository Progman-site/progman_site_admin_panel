<?php
const OK_API_STATUS = 'ok!';
const ERROR_API_STATUS = 'error';

const LANG_SESSION_KEY = 'lang';
const EN_LANGUAGE = 'en';
const RU_LANGUAGE = 'ru';
const AVAILABLE_LANGUAGES = [
    EN_LANGUAGE,
    RU_LANGUAGE,
];
const DEFAULT_LANGUAGE = EN_LANGUAGE;

const ZERO_COURSE_LEVEL = 'zero';
const BEGINNER_COURSE_LEVEL = 'beginner';
const JUNIOR_COURSE_LEVEL = 'junior';
const MIDDLE_COURSE_LEVEL = 'middle';
const COURSE_LEVELS = [
    ZERO_COURSE_LEVEL,
    BEGINNER_COURSE_LEVEL,
    JUNIOR_COURSE_LEVEL,
    MIDDLE_COURSE_LEVEL,
];

const AUTOMATIC_COURSE_TYPE = 'automatic';
const GROUP_ONLINE_COURSE_TYPE = 'group-online';
const INDIVIDUAL_OFFLINE_COURSE_TYPE = 'individual-offline';
const INDIVIDUAL_ONLINE_COURSE_TYPE = 'individual-online';
const COURSE_TYPES = [
    AUTOMATIC_COURSE_TYPE,
    GROUP_ONLINE_COURSE_TYPE,
    INDIVIDUAL_OFFLINE_COURSE_TYPE,
    INDIVIDUAL_ONLINE_COURSE_TYPE,
];
