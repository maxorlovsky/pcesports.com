function GameItemController() {

}

angular.module('app').component('gameItem', {
	templateUrl: 'app/modules/game-item/game-item.html',
	controller: GameItemController,
	bindings: {
		game: '='
	}
});