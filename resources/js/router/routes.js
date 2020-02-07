
function page (path) {
  return () => import(/* webpackChunkName: '' */ `~/pages/${path}`).then(m => m.default || m)
}

export default [
  { path: '/', name: 'welcome', component: page('welcome.vue') },

  { path: '/login', name: 'login', component: page('auth/login.vue') },
  { path: '/register', name: 'register', component: page('auth/register.vue') },
  { path: '/password/reset', name: 'password.request', component: page('auth/password/email.vue') },
  { path: '/password/reset/:token', name: 'password.reset', component: page('auth/password/reset.vue') },

  { path: '/home', name: 'home', component: page('home.vue') },
  { path: '/about', name: 'about', component:  page('about.vue')},
  { path: '/settings',
    component: page('settings/index.vue'),
    children: [
      { path: '', redirect: { name: 'settings.profile' } },
      { path: 'profile', name: 'settings.profile', component: page('settings/profile.vue') },
      { path: 'password', name: 'settings.password', component: page('settings/password.vue') }
    ] },

  { path: '/jobs',
    component: page('jobs/index.vue'), 
    children: [
      { path: '', redirect: { name: 'jobs.list' } },
      { path: 'list', name: 'jobs.list', component: page('jobs/list.vue') },
      { path: 'create', name: 'jobs.create', component: page('jobs/create.vue') },
      { path: 'process/:id', name: 'jobs.process', component: page('jobs/process.vue') },
      { path: 'construct-phylo', name: 'jobs.construct_phylo', component: page('jobs/construct_phylo.vue') },
      { path: 'view-phylo/:id', name: 'jobs.view_phylo', component: page('jobs/view_phylo.vue') }
    ] },

  { path: '/explore', name: 'explore', component: page('explore/index.vue') },
  { path: '/explore/detail/:id', name: 'explore.detail', component: page('explore/detail.vue') },

  {
    path: '/uploads', 
    component: page('uploads/index.vue'), 
    children: [
        { path: '', redirect: { name: 'upload.new' } },
        { path: 'new', name: 'upload.new', component: page('uploads/new.vue') },
        { path: 'list', name: 'upload.list', component: page('uploads/list.vue') }
      ] },

  { path: '*', component: page('errors/404.vue') }
]
