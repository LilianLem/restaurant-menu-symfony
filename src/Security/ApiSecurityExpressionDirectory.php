<?php

namespace App\Security;

abstract class ApiSecurityExpressionDirectory
{
    public const string ADMIN_ONLY = 'is_granted("ROLE_ADMIN")';

    public const string LOGGED_USER = 'is_granted("ROLE_USER")';

    public const string ADMIN_OR_NOT_LOGGED = 'is_granted("ROLE_ADMIN") or not(is_granted("ROLE_USER"))';

    /** Use ADMIN_OR_OWNER_OR_NULL_OBJECT on ApiProperty attributes (see related docs) */
    public const string ADMIN_OR_OWNER = self::ADMIN_ONLY . ' or object?.getOwner() === user';

    public const string ADMIN_OR_OWNER_OR_PUBLIC_OBJECT = self::ADMIN_OR_OWNER . ' or object?.isPublic()';

    /** Relevant on ApiProperty attributes to prevent 500 errors on POST requests (and also PATCH requests at the moment, see #5755 on APIP repo) where object is null (not yet created for POST, not yet retrieved on first steps for PATCH - if understood correctly) */
    public const string ADMIN_OR_OWNER_OR_NULL_OBJECT = self::ADMIN_OR_OWNER . ' or object === null';

    /** Used mainly for alternative "public" collection fields that replace original ones when user doesn't have full rights on the object (that collection is filtered using a custom method instead of the main getter) */
    public const string NOT_ADMIN_NOR_OWNER_AND_NOT_NULL_OBJECT = 'not(is_granted("ROLE_ADMIN")) and object !== null and object.getOwner() !== user';
}
