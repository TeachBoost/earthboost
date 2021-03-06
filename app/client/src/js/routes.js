/**
 * Routes
 *
 * Sets up the route definitions and kicks off the page library.
 */

// Dashboard
page( '/', Router.Dashboard.view );

// Actions
page( '/login', Router.Auth.login );
page( '/logout', Router.Auth.logout );
page( '/authorize', Router.Auth.authorize );
page( '/check-email', Router.Auth.checkEmail );

// Questions
page( '/questions/:group/:year/:userid/:questionid', Router.Questions.view );
page( '/questions/:group/:year/:questionid', Router.Questions.view );
page( '/questions/:group/:year', Router.Questions.view );

// Group pages
page( '/:group/:year', Router.Group.view );
page( '/:group', Router.Group.view );

// Not found
page( '*', Router.notFound );

// Start the router
page();