# Social Authentication Setup Guide

## Google OAuth Setup

1. Go to [Google Cloud Console](https://console.developers.google.com/)
2. Create a new project or select existing one
3. Enable Google+ API
4. Go to "Credentials" → "Create Credentials" → "OAuth 2.0 Client IDs"
5. Set application type to "Web application"
6. Add authorized redirect URIs:
   - `http://localhost:8000/auth/google/callback` (for development)
   - `https://yourdomain.com/auth/google/callback` (for production)
7. Copy the Client ID and Client Secret

<!-- Facebook OAuth has been removed from the application. -->

## Environment Variables

Add these to your `.env` file:

```env
# Google OAuth
GOOGLE_CLIENT_ID=your_google_client_id_here
GOOGLE_CLIENT_SECRET=your_google_client_secret_here
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# Facebook OAuth (removed)
```

## Testing

1. Start your Laravel server: `php artisan serve`
2. Go to `/login` or `/register`
3. Click on "Google" button
4. Complete OAuth flow
5. User should be automatically logged in and redirected to `/appointments`

## Features

- ✅ Google OAuth login/registration
- ❌ Facebook OAuth login/registration (removed)
- ✅ Automatic user creation
- ✅ Email verification bypass for social accounts
- ✅ Profile picture from social accounts
- ✅ Enhanced profile syncing (phone, address when available)
- ✅ Profile completion prompts for missing information
- ✅ Seamless integration with existing auth system

## Profile Syncing

### What Gets Synced Automatically:
- ✅ **Name** - Full name from social account
- ✅ **Email** - Email address (verified)
- ✅ **Profile Picture** - Avatar from social account
- ✅ **Phone** - If available and user grants permission
- ✅ **Address** - If available and user grants permission

### Additional Permissions Required:
To get phone and address information, you need to configure additional scopes:

**Google OAuth:**
- Add scopes: `phone`, `address` in Google Cloud Console
- User must grant permission during OAuth flow

<!-- Facebook OAuth permissions section removed -->

### Profile Completion:
If phone or address is missing after social login, users will be redirected to complete their profile before accessing the main app.
