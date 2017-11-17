'use strict';

// Declare app level module which depends on filters, and services
angular.module('activity',
    [
      'ngRoute',
      'coreModule',
      'bellows.services',
      'ui.bootstrap',
      'sgw.ui.breadcrumb'
    ])
  .controller('ActivityCtrl', ['$scope', '$sce', 'activityPageService', 'linkService',
    'sessionService', 'utilService', 'breadcrumbService',
  function ($scope, $sce, activityService, linkService,
            sessionService, util, breadcrumbService) {
    $scope.getAvatarUrl = util.getAvatarUrl;

    breadcrumbService.set('top', [
      { label: 'Activity' }
    ]);

    $scope.unread = [];

    $scope.isUnread = function (id) {
      return ($.inArray(id, $scope.unread) > -1);
    };

    $scope.decodeActivityList = function (items) {
      for (var i = 0; i < items.length; i++) {
        if ('userRef' in items[i]) {
          items[i].userHref = linkService.user(items[i].userRef.id);
        }

        if ('userRef2' in items[i]) {
          items[i].userHref2 = linkService.user(items[i].userRef2.id);
        }

        if ('projectRef' in items[i]) {
          items[i].projectHref =
            linkService.project(items[i].projectRef.id, items[i].projectRef.type);
        }

        if ('textRef' in items[i]) {
          items[i].textHref = linkService.text(items[i].textRef, items[i].projectRef.id);
        }

        if ('questionRef' in items[i]) {
          items[i].questionHref = linkService.question(items[i].textRef,
            items[i].questionRef, items[i].projectRef.id);
        }

        if ('entryRef' in items[i]) {
          items[i].entryHref = linkService.entry(items[i].entryRef, items[i].projectRef.id);
        }

        if ('content' in items[i]) {
          if ('answer' in items[i].content) {
            items[i].content.answer = $sce.trustAsHtml(items[i].content.answer);
          }

        }
      }
    };

    activityService.list_activity(0, 50, function (result) {
      if (result.ok) {
        $scope.activities = [];
        $scope.unread = result.data.unread;
        for (var key in result.data.activity) {
          if (result.data.activity.hasOwnProperty(key)) {
            $scope.activities.push(result.data.activity[key]);
          }
        }

        $scope.decodeActivityList($scope.activities);
        $scope.filteredActivities = $scope.activities;
      }
    });

    $scope.showAllActivity = true;

    $scope.filterAllActivity = function () {
      $scope.showAllActivity = true;
      $scope.filteredActivities = $scope.activities;
    };

    $scope.filterMyActivity = function () {
      sessionService.getSession().then(function (session) {
        $scope.showAllActivity = false;
        $scope.filteredActivities = [];
        angular.forEach($scope.activities, function (activity) {
          if (activity.userRef && activity.userRef.id === session.userId() ||
            activity.userRef2 && activity.userRef2.id === session.userId()
          ) {
            $scope.filteredActivities.push(activity);
          }
        });
      });
    };
  }]);