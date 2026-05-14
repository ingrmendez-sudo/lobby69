# This is an auto-generated Django model module.
# You'll have to do the following manually to clean this up:
#   * Rearrange models' order
#   * Make sure each model has one field with primary_key=True
#   * Make sure each ForeignKey and OneToOneField has `on_delete` set to the desired behavior
#   * Remove `managed = False` lines if you wish to allow Django to create, modify, and delete the table
# Feel free to rename the models, but don't rename db_table values or field names.
from django.db import models


class AccountEmailaddress(models.Model):
    verified = models.BooleanField()
    primary = models.BooleanField()
    user = models.ForeignKey('UsersUser', models.DO_NOTHING)
    email = models.CharField(unique=True, max_length=254)

    class Meta:
        managed = False
        db_table = 'account_emailaddress'
        unique_together = (('user', 'primary'), ('user', 'email'),)


class AccountEmailconfirmation(models.Model):
    created = models.DateTimeField()
    sent = models.DateTimeField(blank=True, null=True)
    key = models.CharField(unique=True, max_length=64)
    email_address = models.ForeignKey(AccountEmailaddress, models.DO_NOTHING)

    class Meta:
        managed = False
        db_table = 'account_emailconfirmation'


class AuthGroup(models.Model):
    name = models.CharField(unique=True, max_length=150)

    class Meta:
        managed = False
        db_table = 'auth_group'


class AuthGroupPermissions(models.Model):
    group = models.ForeignKey(AuthGroup, models.DO_NOTHING)
    permission = models.ForeignKey('AuthPermission', models.DO_NOTHING)

    class Meta:
        managed = False
        db_table = 'auth_group_permissions'
        unique_together = (('group', 'permission'),)


class AuthPermission(models.Model):
    content_type = models.ForeignKey('DjangoContentType', models.DO_NOTHING)
    codename = models.CharField(max_length=100)
    name = models.CharField(max_length=255)

    class Meta:
        managed = False
        db_table = 'auth_permission'
        unique_together = (('content_type', 'codename'),)


class DjangoAdminLog(models.Model):
    object_id = models.TextField(blank=True, null=True)
    object_repr = models.CharField(max_length=200)
    action_flag = models.PositiveSmallIntegerField()
    change_message = models.TextField()
    content_type = models.ForeignKey('DjangoContentType', models.DO_NOTHING, blank=True, null=True)
    user = models.ForeignKey('UsersUser', models.DO_NOTHING)
    action_time = models.DateTimeField()

    class Meta:
        managed = False
        db_table = 'django_admin_log'


class DjangoContentType(models.Model):
    app_label = models.CharField(max_length=100)
    model = models.CharField(max_length=100)

    class Meta:
        managed = False
        db_table = 'django_content_type'
        unique_together = (('app_label', 'model'),)


class DjangoMigrations(models.Model):
    app = models.CharField(max_length=255)
    name = models.CharField(max_length=255)
    applied = models.DateTimeField()

    class Meta:
        managed = False
        db_table = 'django_migrations'


class DjangoSession(models.Model):
    session_key = models.CharField(primary_key=True, max_length=40)
    session_data = models.TextField()
    expire_date = models.DateTimeField()

    class Meta:
        managed = False
        db_table = 'django_session'


class InvitationsInvitation(models.Model):
    code = models.CharField(unique=True, max_length=32)
    email = models.CharField(max_length=254)
    phone = models.CharField(max_length=20)
    message = models.TextField()
    status = models.CharField(max_length=15)
    used_at = models.DateTimeField(blank=True, null=True)
    expires_at = models.DateTimeField(blank=True, null=True)
    created_at = models.DateTimeField()
    invited_by = models.ForeignKey('UsersUser', models.DO_NOTHING, blank=True, null=True)
    used_by = models.ForeignKey('UsersUser', models.DO_NOTHING, related_name='invitationsinvitation_used_by_set', blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'invitations_invitation'


class InvitationsInvitationrequest(models.Model):
    full_name = models.CharField(max_length=200)
    email = models.CharField(unique=True, max_length=254)
    phone = models.CharField(max_length=20)
    profile_type = models.CharField(max_length=20)
    message = models.TextField()
    age_confirmed = models.BooleanField()
    terms_accepted = models.BooleanField()
    referral_code = models.CharField(max_length=100)
    status = models.CharField(max_length=15)
    reviewed_at = models.DateTimeField(blank=True, null=True)
    admin_notes = models.TextField()
    ip_address = models.CharField(max_length=39, blank=True, null=True)
    created_at = models.DateTimeField()
    reviewed_by = models.ForeignKey('UsersUser', models.DO_NOTHING, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'invitations_invitationrequest'


class MembershipsMembershipplan(models.Model):
    name = models.CharField(max_length=50)
    plan_type = models.CharField(unique=True, max_length=10)
    price_mxn = models.DecimalField(max_digits=10, decimal_places=5)  # max_digits and decimal_places have been guessed, as this database handles decimal fields as float
    price_mxn_monthly = models.DecimalField(max_digits=10, decimal_places=5)  # max_digits and decimal_places have been guessed, as this database handles decimal fields as float
    description = models.TextField()
    features = models.JSONField()
    max_photos = models.PositiveIntegerField()
    max_videos = models.PositiveIntegerField()
    max_messages_per_day = models.PositiveIntegerField()
    can_view_private_photos = models.BooleanField()
    can_send_invitations = models.BooleanField()
    can_join_events = models.BooleanField()
    can_create_events = models.BooleanField()
    profile_badge = models.CharField(max_length=50)
    is_active = models.BooleanField()
    sort_order = models.PositiveSmallIntegerField()

    class Meta:
        managed = False
        db_table = 'memberships_membershipplan'


class MembershipsUsermembership(models.Model):
    status = models.CharField(max_length=10)
    starts_at = models.DateTimeField()
    expires_at = models.DateTimeField(blank=True, null=True)
    auto_renew = models.BooleanField()
    created_at = models.DateTimeField()
    updated_at = models.DateTimeField()
    plan = models.ForeignKey(MembershipsMembershipplan, models.DO_NOTHING)
    user = models.OneToOneField('UsersUser', models.DO_NOTHING)

    class Meta:
        managed = False
        db_table = 'memberships_usermembership'


class MfaAuthenticator(models.Model):
    type = models.CharField(max_length=20)
    data = models.JSONField()
    user = models.ForeignKey('UsersUser', models.DO_NOTHING)
    created_at = models.DateTimeField()
    last_used_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'mfa_authenticator'
        unique_together = (('user', 'type'),)


class ModerationModerationlog(models.Model):
    action = models.CharField(max_length=10)
    notes = models.TextField()
    created_at = models.DateTimeField()
    moderator = models.ForeignKey('UsersUser', models.DO_NOTHING)
    target_user = models.ForeignKey('UsersUser', models.DO_NOTHING, related_name='moderationmoderationlog_target_user_set', blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'moderation_moderationlog'


class ModerationReport(models.Model):
    object_id = models.PositiveBigIntegerField(blank=True, null=True)
    reason = models.CharField(max_length=15)
    description = models.TextField()
    status = models.CharField(max_length=12)
    reviewed_at = models.DateTimeField(blank=True, null=True)
    moderator_notes = models.TextField()
    created_at = models.DateTimeField()
    content_type = models.ForeignKey(DjangoContentType, models.DO_NOTHING, blank=True, null=True)
    reporter = models.ForeignKey('UsersUser', models.DO_NOTHING)
    reviewed_by = models.ForeignKey('UsersUser', models.DO_NOTHING, related_name='moderationreport_reviewed_by_set', blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'moderation_report'


class PaymentsPayment(models.Model):
    reference = models.CharField(unique=True, max_length=32)
    amount_mxn = models.DecimalField(max_digits=10, decimal_places=5)  # max_digits and decimal_places have been guessed, as this database handles decimal fields as float
    method = models.CharField(max_length=15)
    status = models.CharField(max_length=12)
    provider_ref = models.CharField(max_length=255)
    provider_response = models.JSONField()
    receipt_image = models.CharField(max_length=100, blank=True, null=True)
    receipt_verified = models.BooleanField()
    receipt_verified_at = models.DateTimeField(blank=True, null=True)
    notes = models.TextField()
    created_at = models.DateTimeField()
    updated_at = models.DateTimeField()
    plan = models.ForeignKey(MembershipsMembershipplan, models.DO_NOTHING)
    receipt_verified_by = models.ForeignKey('UsersUser', models.DO_NOTHING, blank=True, null=True)
    user = models.ForeignKey('UsersUser', models.DO_NOTHING, related_name='paymentspayment_user_set')

    class Meta:
        managed = False
        db_table = 'payments_payment'


class PrivateMessagingConversation(models.Model):
    created_at = models.DateTimeField()
    updated_at = models.DateTimeField()
    last_message_at = models.DateTimeField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'private_messaging_conversation'


class PrivateMessagingConversationParticipants(models.Model):
    conversation = models.ForeignKey(PrivateMessagingConversation, models.DO_NOTHING)
    user = models.ForeignKey('UsersUser', models.DO_NOTHING)

    class Meta:
        managed = False
        db_table = 'private_messaging_conversation_participants'
        unique_together = (('conversation', 'user'),)


class PrivateMessagingMessage(models.Model):
    message_type = models.CharField(max_length=10)
    content = models.TextField()
    image = models.CharField(max_length=100, blank=True, null=True)
    audio = models.CharField(max_length=100, blank=True, null=True)
    is_read = models.BooleanField()
    read_at = models.DateTimeField(blank=True, null=True)
    is_deleted_sender = models.BooleanField()
    is_deleted_receiver = models.BooleanField()
    created_at = models.DateTimeField()
    conversation = models.ForeignKey(PrivateMessagingConversation, models.DO_NOTHING)
    sender = models.ForeignKey('UsersUser', models.DO_NOTHING)

    class Meta:
        managed = False
        db_table = 'private_messaging_message'


class ProfilesConnection(models.Model):
    status = models.CharField(max_length=15)
    created_at = models.DateTimeField()
    updated_at = models.DateTimeField()
    from_profile = models.ForeignKey('ProfilesProfile', models.DO_NOTHING)
    to_profile = models.ForeignKey('ProfilesProfile', models.DO_NOTHING, related_name='profilesconnection_to_profile_set')

    class Meta:
        managed = False
        db_table = 'profiles_connection'
        unique_together = (('from_profile', 'to_profile'),)


class ProfilesProfile(models.Model):
    profile_type = models.CharField(max_length=20)
    status = models.CharField(max_length=20)
    nickname = models.CharField(unique=True, max_length=50)
    bio = models.TextField()
    birth_date = models.DateField(blank=True, null=True)
    gender = models.CharField(max_length=20)
    partner_nickname = models.CharField(max_length=50)
    partner_birth_date = models.DateField(blank=True, null=True)
    partner_gender = models.CharField(max_length=20)
    state = models.CharField(max_length=10)
    city = models.CharField(max_length=100)
    preferences = models.JSONField()
    looking_for = models.CharField(max_length=200)
    avatar = models.CharField(max_length=100, blank=True, null=True)
    cover_photo = models.CharField(max_length=100, blank=True, null=True)
    is_private = models.BooleanField()
    show_location = models.BooleanField()
    show_age = models.BooleanField()
    views_count = models.PositiveIntegerField()
    connections_count = models.PositiveIntegerField()
    age_verified = models.BooleanField()
    terms_accepted = models.BooleanField()
    terms_accepted_at = models.DateTimeField(blank=True, null=True)
    created_at = models.DateTimeField()
    updated_at = models.DateTimeField()
    last_seen = models.DateTimeField(blank=True, null=True)
    user = models.OneToOneField('UsersUser', models.DO_NOTHING)

    class Meta:
        managed = False
        db_table = 'profiles_profile'


class SocialFeedComment(models.Model):
    content = models.TextField()
    is_approved = models.BooleanField()
    created_at = models.DateTimeField()
    author = models.ForeignKey('UsersUser', models.DO_NOTHING)
    parent = models.ForeignKey('self', models.DO_NOTHING, blank=True, null=True)
    post = models.ForeignKey('SocialFeedPost', models.DO_NOTHING)

    class Meta:
        managed = False
        db_table = 'social_feed_comment'


class SocialFeedDailyplan(models.Model):
    title = models.CharField(max_length=200)
    plan_type = models.CharField(max_length=20)
    description = models.TextField()
    date = models.DateField()
    start_time = models.TimeField()
    end_time = models.TimeField(blank=True, null=True)
    location = models.CharField(max_length=200)
    max_participants = models.PositiveIntegerField(blank=True, null=True)
    is_members_only = models.BooleanField()
    min_membership = models.CharField(max_length=10)
    image = models.CharField(max_length=100, blank=True, null=True)
    created_at = models.DateTimeField()

    class Meta:
        managed = False
        db_table = 'social_feed_dailyplan'


class SocialFeedPost(models.Model):
    post_type = models.CharField(max_length=10)
    content = models.TextField()
    visibility = models.CharField(max_length=12)
    image = models.CharField(max_length=100, blank=True, null=True)
    video = models.CharField(max_length=100, blank=True, null=True)
    is_approved = models.BooleanField()
    is_nsfw = models.BooleanField()
    moderated_at = models.DateTimeField(blank=True, null=True)
    likes_count = models.PositiveIntegerField()
    comments_count = models.PositiveIntegerField()
    views_count = models.PositiveIntegerField()
    created_at = models.DateTimeField()
    updated_at = models.DateTimeField()
    is_deleted = models.BooleanField()
    author = models.ForeignKey('UsersUser', models.DO_NOTHING)
    moderated_by = models.ForeignKey('UsersUser', models.DO_NOTHING, related_name='socialfeedpost_moderated_by_set', blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'social_feed_post'


class SocialFeedPostreaction(models.Model):
    reaction_type = models.CharField(max_length=10)
    created_at = models.DateTimeField()
    post = models.ForeignKey(SocialFeedPost, models.DO_NOTHING)
    user = models.ForeignKey('UsersUser', models.DO_NOTHING)

    class Meta:
        managed = False
        db_table = 'social_feed_postreaction'
        unique_together = (('post', 'user'),)


class SocialFeedStory(models.Model):
    image = models.CharField(max_length=100, blank=True, null=True)
    video = models.CharField(max_length=100, blank=True, null=True)
    caption = models.CharField(max_length=200)
    is_nsfw = models.BooleanField()
    views_count = models.PositiveIntegerField()
    created_at = models.DateTimeField()
    expires_at = models.DateTimeField()
    author = models.ForeignKey('UsersUser', models.DO_NOTHING)

    class Meta:
        managed = False
        db_table = 'social_feed_story'


class SocialaccountSocialaccount(models.Model):
    provider = models.CharField(max_length=200)
    uid = models.CharField(max_length=191)
    last_login = models.DateTimeField()
    date_joined = models.DateTimeField()
    user = models.ForeignKey('UsersUser', models.DO_NOTHING)
    extra_data = models.JSONField()

    class Meta:
        managed = False
        db_table = 'socialaccount_socialaccount'
        unique_together = (('provider', 'uid'),)


class SocialaccountSocialapp(models.Model):
    provider = models.CharField(max_length=30)
    name = models.CharField(max_length=40)
    client_id = models.CharField(max_length=191)
    secret = models.CharField(max_length=191)
    key = models.CharField(max_length=191)
    provider_id = models.CharField(max_length=200)
    settings = models.JSONField()

    class Meta:
        managed = False
        db_table = 'socialaccount_socialapp'


class SocialaccountSocialtoken(models.Model):
    token = models.TextField()
    token_secret = models.TextField()
    expires_at = models.DateTimeField(blank=True, null=True)
    account = models.ForeignKey(SocialaccountSocialaccount, models.DO_NOTHING)
    app = models.ForeignKey(SocialaccountSocialapp, models.DO_NOTHING, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'socialaccount_socialtoken'
        unique_together = (('app', 'account'),)


class UsersUser(models.Model):
    password = models.CharField(max_length=128)
    last_login = models.DateTimeField(blank=True, null=True)
    is_superuser = models.BooleanField()
    username = models.CharField(unique=True, max_length=150)
    email = models.CharField(max_length=254)
    is_staff = models.BooleanField()
    is_active = models.BooleanField()
    date_joined = models.DateTimeField()
    name = models.CharField(max_length=255)

    class Meta:
        managed = False
        db_table = 'users_user'


class UsersUserGroups(models.Model):
    user = models.ForeignKey(UsersUser, models.DO_NOTHING)
    group = models.ForeignKey(AuthGroup, models.DO_NOTHING)

    class Meta:
        managed = False
        db_table = 'users_user_groups'
        unique_together = (('user', 'group'),)


class UsersUserUserPermissions(models.Model):
    user = models.ForeignKey(UsersUser, models.DO_NOTHING)
    permission = models.ForeignKey(AuthPermission, models.DO_NOTHING)

    class Meta:
        managed = False
        db_table = 'users_user_user_permissions'
        unique_together = (('user', 'permission'),)


class VerificationAgeverification(models.Model):
    method = models.CharField(max_length=15)
    status = models.CharField(max_length=10)
    document_front = models.CharField(max_length=100, blank=True, null=True)
    document_back = models.CharField(max_length=100, blank=True, null=True)
    selfie_with_id = models.CharField(max_length=100, blank=True, null=True)
    declared_birth_date = models.DateField(blank=True, null=True)
    declared_name = models.CharField(max_length=200)
    reviewed_at = models.DateTimeField(blank=True, null=True)
    rejection_reason = models.TextField()
    consent_given = models.BooleanField()
    consent_ip = models.CharField(max_length=39, blank=True, null=True)
    consent_at = models.DateTimeField(blank=True, null=True)
    created_at = models.DateTimeField()
    updated_at = models.DateTimeField()
    reviewed_by = models.ForeignKey(UsersUser, models.DO_NOTHING, blank=True, null=True)
    user = models.OneToOneField(UsersUser, models.DO_NOTHING, related_name='verificationageverification_user_set')

    class Meta:
        managed = False
        db_table = 'verification_ageverification'
