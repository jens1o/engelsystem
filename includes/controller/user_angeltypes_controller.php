<?php

/**
 * Display a hint for team/angeltype supporters if there are unconfirmed users for his angeltype.
 *
 * @return string|null
 */
function user_angeltypes_unconfirmed_hint()
{
    global $user;

    $unconfirmed_user_angeltypes = User_unconfirmed_AngelTypes($user);
    if (count($unconfirmed_user_angeltypes) == 0) {
        return null;
    }

    $unconfirmed_links = [];
    foreach ($unconfirmed_user_angeltypes as $user_angeltype) {
        $unconfirmed_links[] = '<a href="'
            . page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $user_angeltype['angeltype_id']])
            . '">' . $user_angeltype['name']
            . ' (+' . $user_angeltype['count'] . ')'
            . '</a>';
    }

    return sprintf(ngettext('There is %d unconfirmed angeltype.', 'There are %d unconfirmed angeltypes.',
            count($unconfirmed_user_angeltypes)),
            count($unconfirmed_user_angeltypes)) . ' ' . _('Angel types which need approvals:') . ' ' . join(', ',
            $unconfirmed_links);
}

/**
 * Remove all unconfirmed users from a specific angeltype.
 *
 * @return array
 */
function user_angeltypes_delete_all_controller()
{
    global $user;
    $request = request();

    if (!$request->has('angeltype_id')) {
        error(_('Angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    $angeltype = AngelType($request->input('angeltype_id'));
    if ($angeltype == null) {
        error(_('Angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    if (!User_is_AngelType_supporter($user, $angeltype)) {
        error(_('You are not allowed to delete all users for this angeltype.'));
        redirect(page_link_to('angeltypes'));
    }

    if ($request->has('confirmed')) {
        UserAngelTypes_delete_all($angeltype['id']);

        engelsystem_log(sprintf('Denied all users for angeltype %s', AngelType_name_render($angeltype)));
        success(sprintf(_('Denied all users for angeltype %s.'), AngelType_name_render($angeltype)));
        redirect(page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype['id']]));
    }

    return [
        _('Deny all users'),
        UserAngelTypes_delete_all_view($angeltype)
    ];
}

/**
 * Confirm all unconfirmed users for an angeltype.
 *
 * @return array
 */
function user_angeltypes_confirm_all_controller()
{
    global $user, $privileges;
    $request = request();

    if (!$request->has('angeltype_id')) {
        error(_('Angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    $angeltype = AngelType($request->input('angeltype_id'));
    if ($angeltype == null) {
        error(_('Angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    if (!in_array('admin_user_angeltypes', $privileges) && !User_is_AngelType_supporter($user, $angeltype)) {
        error(_('You are not allowed to confirm all users for this angeltype.'));
        redirect(page_link_to('angeltypes'));
    }

    if ($request->has('confirmed')) {
        UserAngelTypes_confirm_all($angeltype['id'], $user);

        engelsystem_log(sprintf('Confirmed all users for angeltype %s', AngelType_name_render($angeltype)));
        success(sprintf(_('Confirmed all users for angeltype %s.'), AngelType_name_render($angeltype)));
        redirect(page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype['id']]));
    }

    return [
        _('Confirm all users'),
        UserAngelTypes_confirm_all_view($angeltype)
    ];
}

/**
 * Confirm an user for an angeltype.
 *
 * @return array
 */
function user_angeltype_confirm_controller()
{
    global $user;
    $request = request();

    if (!$request->has('user_angeltype_id')) {
        error(_('User angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    $user_angeltype = UserAngelType($request->input('user_angeltype_id'));
    if ($user_angeltype == null) {
        error(_('User angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    $angeltype = AngelType($user_angeltype['angeltype_id']);
    if ($angeltype == null) {
        error(_('Angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    if (!User_is_AngelType_supporter($user, $angeltype)) {
        error(_('You are not allowed to confirm this users angeltype.'));
        redirect(page_link_to('angeltypes'));
    }

    $user_source = User($user_angeltype['user_id']);
    if ($user_source == null) {
        error(_('User doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    if ($request->has('confirmed')) {
        UserAngelType_confirm($user_angeltype['id'], $user);

        engelsystem_log(sprintf(
            '%s confirmed for angeltype %s',
            User_Nick_render($user_source),
            AngelType_name_render($angeltype)
        ));
        success(sprintf(
            _('%s confirmed for angeltype %s.'),
            User_Nick_render($user_source),
            AngelType_name_render($angeltype)
        ));
        redirect(page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype['id']]));
    }

    return [
        _('Confirm angeltype for user'),
        UserAngelType_confirm_view($user_angeltype, $user_source, $angeltype)
    ];
}

/**
 * Remove a user from an Angeltype.
 *
 * @return array
 */
function user_angeltype_delete_controller()
{
    global $user;
    $request = request();

    if (!$request->has('user_angeltype_id')) {
        error(_('User angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    $user_angeltype = UserAngelType($request->input('user_angeltype_id'));
    if ($user_angeltype == null) {
        error(_('User angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    $angeltype = AngelType($user_angeltype['angeltype_id']);
    if ($angeltype == null) {
        error(_('Angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    $user_source = User($user_angeltype['user_id']);
    if ($user_source == null) {
        error(_('User doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    if ($user['UID'] != $user_angeltype['user_id'] && !User_is_AngelType_supporter($user, $angeltype)) {
        error(_('You are not allowed to delete this users angeltype.'));
        redirect(page_link_to('angeltypes'));
    }

    if ($request->has('confirmed')) {
        UserAngelType_delete($user_angeltype);

        $success_message = sprintf(_('User %s removed from %s.'), User_Nick_render($user_source), $angeltype['name']);
        engelsystem_log($success_message);
        success($success_message);

        redirect(page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype['id']]));
    }

    return [
        _('Remove angeltype'),
        UserAngelType_delete_view($user_angeltype, $user_source, $angeltype)
    ];
}

/**
 * Update an UserAngelType.
 *
 * @return array
 */
function user_angeltype_update_controller()
{
    global $privileges;
    $supporter = false;
    $request = request();

    if (!in_array('admin_angel_types', $privileges)) {
        error(_('You are not allowed to set supporter rights.'));
        redirect(page_link_to('angeltypes'));
    }

    if (!$request->has('user_angeltype_id')) {
        error(_('User angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    if ($request->has('supporter') && preg_match('/^[01]$/', $request->input('supporter'))) {
        $supporter = $request->input('supporter') == '1';
    } else {
        error(_('No supporter update given.'));
        redirect(page_link_to('angeltypes'));
    }

    $user_angeltype = UserAngelType($request->input('user_angeltype_id'));
    if ($user_angeltype == null) {
        error(_('User angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    $angeltype = AngelType($user_angeltype['angeltype_id']);
    if ($angeltype == null) {
        error(_('Angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    $user_source = User($user_angeltype['user_id']);
    if ($user_source == null) {
        error(_('User doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    if ($request->has('confirmed')) {
        UserAngelType_update($user_angeltype['id'], $supporter);

        $success_message = sprintf(
            $supporter
                ? _('Added supporter rights for %s to %s.')
                : _('Removed supporter rights for %s from %s.'),
            AngelType_name_render($angeltype),
            User_Nick_render($user_source)
        );
        engelsystem_log($success_message);
        success($success_message);

        redirect(page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype['id']]));
    }

    return [
        $supporter ? _('Add supporter rights') : _('Remove supporter rights'),
        UserAngelType_update_view($user_angeltype, $user_source, $angeltype, $supporter)
    ];
}

/**
 * User joining an Angeltype (Or supporter doing this for him).
 *
 * @return array
 */
function user_angeltype_add_controller()
{
    global $user;
    $angeltype = load_angeltype();

    // User is joining by itself
    if (!User_is_AngelType_supporter($user, $angeltype)) {
        return user_angeltype_join_controller($angeltype);
    }

    // Allow to add any user

    // Default selection
    $user_source = $user;

    // Load possible users, that are not in the angeltype already
    $users_source = Users_by_angeltype_inverted($angeltype);

    if (request()->has('submit')) {
        $user_source = load_user();

        if (!UserAngelType_exists($user_source, $angeltype)) {
            $user_angeltype_id = UserAngelType_create($user_source, $angeltype);

            engelsystem_log(sprintf(
                'User %s added to %s.',
                User_Nick_render($user_source),
                AngelType_name_render($angeltype)
            ));
            success(sprintf(
                _('User %s added to %s.'),
                User_Nick_render($user_source),
                AngelType_name_render($angeltype)
            ));

            UserAngelType_confirm($user_angeltype_id, $user_source);
            engelsystem_log(sprintf(
                'User %s confirmed as %s.',
                User_Nick_render($user_source),
                AngelType_name_render($angeltype)
            ));

            redirect(page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype['id']]));
        }
    }

    return [
        _('Add user to angeltype'),
        UserAngelType_add_view($angeltype, $users_source, $user_source['UID'])
    ];
}

/**
 * A user joins an angeltype.
 *
 * @param array $angeltype
 * @return array
 */
function user_angeltype_join_controller($angeltype)
{
    global $user, $privileges;

    $user_angeltype = UserAngelType_by_User_and_AngelType($user, $angeltype);
    if ($user_angeltype != null) {
        error(sprintf(_('You are already a %s.'), $angeltype['name']));
        redirect(page_link_to('angeltypes'));
    }

    if (request()->has('confirmed')) {
        $user_angeltype_id = UserAngelType_create($user, $angeltype);

        $success_message = sprintf(_('You joined %s.'), $angeltype['name']);
        engelsystem_log(sprintf(
            'User %s joined %s.',
            User_Nick_render($user),
            AngelType_name_render($angeltype)
        ));
        success($success_message);

        if (in_array('admin_user_angeltypes', $privileges)) {
            UserAngelType_confirm($user_angeltype_id, $user);
            engelsystem_log(sprintf(
                'User %s confirmed as %s.',
                User_Nick_render($user),
                AngelType_name_render($angeltype)
            ));
        }

        redirect(page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype['id']]));
    }

    return [
        sprintf(_('Become a %s'), $angeltype['name']),
        UserAngelType_join_view($user, $angeltype)
    ];
}

/**
 * Route UserAngelType actions.
 *
 * @return array
 */
function user_angeltypes_controller()
{
    $request = request();
    if (!$request->has('action')) {
        redirect(page_link_to('angeltypes'));
    }

    switch ($request->input('action')) {
        case 'delete_all':
            return user_angeltypes_delete_all_controller();
        case 'confirm_all':
            return user_angeltypes_confirm_all_controller();
        case 'confirm':
            return user_angeltype_confirm_controller();
        case 'delete':
            return user_angeltype_delete_controller();
        case 'update':
            return user_angeltype_update_controller();
        case 'add':
            return user_angeltype_add_controller();
        default:
            redirect(page_link_to('angeltypes'));
    }
}
