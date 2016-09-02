<?php

$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

switch ($act):
/**
 * Главная страница
 */
case 'index':

    $total = DBM::run()->count('guest');

    if ($total > 0 && $start >= $total) {
        $start = last_page($total, $config['bookpost']);
    }

    $page = floor(1 + $start / $config['bookpost']);
    $config['newtitle'] = 'Гостевая книга (Стр. '.$page.')';

    $posts = DBM::run()->select('guest', null, $config['bookpost'], $start, array('guest_time'=>'DESC'));


    App::view('book/index', compact('posts', 'start', 'total'));
break;

/**
 * Добавление сообщения
 */
case 'add':

    $msg = check(Request::input('msg'));
    $uid = check(Request::input('uid'));

    $validation = new Validation();
    $validation->addRule('equal', [$uid, $_SESSION['token']], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
        ->addRule('string', $msg, ['msg' => 'Ошибка! Слишком длинное или короткое сообщение!'], true, 5, $config['guesttextlength'])
        ->addRule('bool', is_user(), ['msg' => 'Для добавления сообщения необходимо авторизоваться'])
        ->addRule('bool', is_flood($log), ['msg' => 'Антифлуд! Разрешается отправлять сообщения раз в '.flood_period().' секунд!']);

    /* Проерка для гостей */
    if (! is_user() && $config['bookadds']) {
        $provkod = check(strtolower($_POST['provkod']));

        $validation->addRule('bool', is_user(), 'Для добавления сообщения необходимо авторизоваться');
        $validation->addRule('equal', [$provkod, $_SESSION['protect']], 'Проверочное число не совпало с данными на картинке!');
    }

    if ($validation->run()) {

        $msg = antimat($msg);

        if (is_user()) {
            $bookscores = ($config['bookscores']) ? 1 : 0;

            $user = DBM::run()->update('users', array(
                'users_allguest' => array('+', 1),
                'users_point' => array('+', $bookscores),
                'users_money' => array('+', 5),
            ), array(
                'users_login' => $log
            ));
        }

        $username = is_user() ? $log : $config['guestsuser'];

        $guest = DBM::run()->insert('guest', array(
            'guest_user' => $username,
            'guest_text' => $msg,
            'guest_ip'   => App::getClientIp(),
            'guest_brow' => App::getUserAgent(),
            'guest_time' => SITETIME,
        ));

        DBM::run()->execute("DELETE FROM `guest` WHERE `guest_time` < (SELECT MIN(`guest_time`) FROM (SELECT `guest_time` FROM `guest` ORDER BY `guest_time` DESC LIMIT :limit) AS del);", array('limit' => intval($config['maxpostbook'])));

        App::setFlash('success', 'Сообщение успешно добавлено!');
    } else {
        App::setInput(Request::all());
        App::setFlash('danger', $validation->getErrors());
    }

    App::redirect("/book");
break;

/**
 * Подготовка к редактированию
 */
case 'edit':
    $id  = isset($params['id']) ? abs(intval($params['id'])) : 0;

    if (! is_user()) App::abort(403);

    $post = DBM::run()->selectFirst('guest', array('guest_id' => $id, 'guest_user' =>$log));

    if (! $post) {
        App::abort('default', 'Ошибка! Сообщение удалено или вы не автор этого сообщения!');
    }

    if ($post['guest_time'] + 600 < SITETIME) {
        App::abort('default', 'Редактирование невозможно, прошло более 10 минут!');
    }

    if (Request::isMethod('post')) {

        $msg = check(Request::input('msg'));
        $uid = check(Request::input('uid'));

        $validation = new Validation();
        $validation->addRule('equal', [$uid, $_SESSION['token']], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
            ->addRule('string', $msg, ['msg' => 'Ошибка! Слишком длинное или короткое сообщение!'], true, 5, $config['guesttextlength']);

        if ($validation->run()) {

            $msg = antimat($msg);

            $guest = DBM::run()->update('guest', array(
                'guest_text'      => $msg,
                'guest_edit'      => $log,
                'guest_edit_time' => SITETIME,
            ), array(
                'guest_id' => $id
            ));

            App::setFlash('success', 'Сообщение успешно отредактировано!');
            App::redirect("/book");
        } else {
            App::setInput(Request::all());
            App::setFlash('danger', $validation->getErrors());
        }
    }

    App::view('book/edit', compact('post', 'id'));
break;

/**
 * Жалоба
 */
case 'complaint':
    $id = abs(intval(Request::input('id')));

    if (! Request::ajax()) App::redirect('/');

    $uid = check(Request::input('token'));

    $validation = new Validation();
    $validation->addRule('equal', [$uid, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        ->addRule('bool', is_user(), 'Для отправки жалобы необходимо авторизоваться');

    $data = DBM::run()->selectFirst('guest', array('guest_id' => $id));
    $validation->addRule('custom', $data, 'Выбранное вами сообщение для жалобы не существует!');


    $spam = DBM::run()->selectFirst('spam', array('spam_key' => 2, 'spam_idnum' => $id));
    $validation->addRule('custom', !$spam, 'Жалоба на данное сообщение уже отправлена!');

    if ($validation->run()) {
        $spam = DBM::run()->insert('spam', array(
            'spam_key'     => 2,
            'spam_idnum'   => $data['guest_id'],
            'spam_user'    => $log,
            'spam_login'   => $data['guest_user'],
            'spam_text'    => $data['guest_text'],
            'spam_time'    => $data['guest_time'],
            'spam_addtime' => SITETIME,
            'spam_link'    => '/book/index.php?start='.$start,
        ));

        exit(json_encode(['status' => 'success']));
    } else {
        exit(json_encode(['status' => 'error', 'message' => current($validation->getErrors())]));
    }
    break;
endswitch;