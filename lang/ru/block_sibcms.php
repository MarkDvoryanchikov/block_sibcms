<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * block_sibcms
 *
 * @package    block_sibcms
 * @copyright  2017 Sergey Shlyanin <sergei.shlyanin@gmail.com>, Aleksandr Raetskiy <ksenon3@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Система мониторинга курсов';
$string['sibcms:myaddinstance'] = 'Добавлять новый блок «Мониторинг курсов» на домашнюю страницу';
$string['sibcms:addinstance'] = 'Добавлять новый блок «Мониторинг курсов»';
$string['sibcms:monitoring'] = 'Осуществлять мониторинг курсов';
$string['sibcms:activity_report'] = 'Просматривать отчет о работе в курсе';
$string['sibcms:teacher_report'] = 'Просматривать отчет по курсам преподавателя';
$string['sibcms:monitoring_report'] = 'Просматривать отчет «Мониторинг курсов»';
$string['sibcms:monitoring_report_category'] = 'Просматривать категорию отчета «Мониторинг курсов»';
$string['sibcms:activity_report_toggle'] = 'Переключать видимость элементов отчета «Работа в курсе»';
$string['key1'] = 'Мониторинг курсов';
$string['key2'] = 'Категория';
$string['key3'] = 'Количество курсов';
$string['key4'] = 'Требуют внимания';
$string['key6'] = 'Категория не существует или не содержит подкатегорий';
$string['key7'] = 'К родительской категории';
$string['key8'] = 'Курсы';
$string['key9'] = 'Просмотреть курсы';
$string['key10'] = 'Курс';
$string['key11'] = 'Последняя проверка';
$string['key13'] = 'Оценка';
$string['key19'] = 'Оценить';
$string['key20'] = 'Мониторинг курса';
$string['key21'] = 'Мониторинг курсов';
$string['key22'] = 'Мониторинг курсов: {$a->name}';
$string['key23'] = 'Замечаний не обнаружено';
$string['key27'] = 'Курс';
$string['key28'] = 'Преподаватели';
$string['key29'] = 'Комментарий к курсу';
$string['key32'] = 'Сохранить и вернуться';
$string['key34'] = 'Комментарий к курсу';
$string['key35'] = 'Дополнительная информация';
$string['key36'] = 'Информация о тестах';
$string['key37'] = 'Информация о заданиях';
$string['key38'] = 'Задание';
$string['key39'] = 'Общее количество работ';
$string['key40'] = 'Число загруженных работ';
$string['key41'] = 'Процент загруженных работ';
$string['key42'] = 'Число оцененных работ';
$string['key43'] = 'Процент оцененных работ';
$string['key44'] = 'Оценка';
$string['key45'] = 'Отзыв';
$string['key46'] = 'Тест';
$string['key47'] = 'Общее количество тестов';
$string['key48'] = 'Число выполненных тестов';
$string['key49'] = 'Процент выполненных тестов';
$string['key50'] = 'Нет преподавателей';
$string['key51'] = 'Студенты не подписаны на курс';
$string['key52'] = 'В курсе нет ни одного файла';
$string['key53'] = 'В курсе нет ни одного задания или теста';
$string['key54'] = 'Некоторые задания не имеют оценки';
$string['key55'] = 'В некоторых тестах нет ни одного вопроса';
$string['key56'] = 'Без оценки';
$string['key57'] = 'Баллы: {$a->points}';
$string['key58'] = 'Замечаний не обнаружено';
$string['key59'] = 'Вы не можете просматривать эту страницу';
$string['key60'] = 'Есть неоцененные работы ({$a->count})';
$string['key61'] = 'Работа в курсе';
$string['key62'] = 'Эффективность работы по курсу: {$a}';
$string['key63'] = 'Итого';
$string['key64'] = 'Отчет по категории';
$string['key65'] = 'Завершенность курса: {$a}';
$string['key66'] = 'Преподаватель';
$string['key67'] = 'Последний доступ к курсу';
$string['key68'] = 'Завершенность курса';
$string['key69'] = 'Отчет «Работа в курсе» просмотрен';
$string['key70'] = 'Отчет «Мониторинг курсов» просмотрен';
$string['key71'] = 'Отзыв создан';
$string['key72'] = 'Есть неоцененные работы';
$string['key73'] = 'Групповой режим';
$string['key74'] = 'Без вопросов';
$string['key75'] = 'Нет отзыва';
$string['key76'] = 'Курс не просматривался администратором';
$string['key77'] = 'Просмотрен';
$string['key82'] = 'В некоторых заданиях отключены отзывы';
$string['key83'] = 'Категория с id {$a->category} не содержит курс с id {$a->course}';
$string['key84'] = 'Сохранить и перейти к следующему курсу';
$string['key85'] = 'В категории больше нет курсов, требующих внимания';
$string['key86'] = 'Данные о модулях курса';
$string['key87'] = 'Оценивание курса';
$string['key88'] = 'Последняя проверка';
$string['key89'] = 'Отображение курса';
$string['key90'] = 'Некоторые из преподавателей не заходили в курс';
$string['key91'] = 'Отчет по преподавателям';
$string['key94'] = 'Ограничение по времени';
$string['key95'] = 'В некоторых тестах нет ограничения по времени';
$string['key96'] = 'Нет ограничения по времени';
$string['key97'] = 'Количество курсов без замечаний';
$string['key98'] = 'Количество курсов, где студенты сдают работы';
$string['key99'] = 'Количество курсов, где преподаватели проверяют работы';
$string['key100'] = 'Актуальность отзывов';
$string['key101'] = 'Срок актуальности отзывов. По истечении это срока курс требует внимания';
$string['key102'] = 'Свойство';
$string['key103'] = 'Настройки';
$string['key104'] = 'Добавить новое свойство';
$string['key105'] = 'Свойства курсов';
$string['key106'] = 'Курс был изменен';
$string['key107'] = 'Неизвестно';
$string['key108'] = 'Включена проверка на плагиат';
$string['key109'] = 'Обнаруженные замечания';
$string['key110'] = 'Содержание курса изменено';
$string['key111'] = 'Преподаватель оценил работы';
$string['key112'] = 'Мои курсы';