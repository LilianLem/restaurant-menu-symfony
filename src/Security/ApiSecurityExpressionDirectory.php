<?php

namespace App\Security;

abstract class ApiSecurityExpressionDirectory
{
    public const string ADMIN_ONLY = 'is_granted("ROLE_ADMIN")';

    public const string LOGGED_USER = 'is_granted("ROLE_USER")';

    /** Use ADMIN_OR_OWNER_OR_NULL_OBJECT on ApiProperty attributes (see related docs) */
    public const string ADMIN_OR_OWNER = self::ADMIN_ONLY . ' or object?.getOwner() === user';

    public const string ADMIN_OR_OWNER_OR_PUBLIC_OBJECT = self::ADMIN_OR_OWNER . ' or object?.isPublic()';

    /** Relevant on ApiProperty attributes to prevent 500 errors on POST requests where object is null (not yet created) */
    public const string ADMIN_OR_OWNER_OR_NULL_OBJECT = self::ADMIN_OR_OWNER . ' or object === null';
}
