export default defineNuxtRouteMiddleware(async (to) => {
  const { user, ensureCurrentUserLoaded } = useCurrentUser()
  await ensureCurrentUserLoaded()

  if (!user.value) {
    return navigateTo(`/login?redirect=${encodeURIComponent(to.fullPath)}`)
  }

  if (!user.value.is_super_admin) {
    throw createError({
      statusCode: 403,
      statusMessage: 'Forbidden'
    })
  }
})
