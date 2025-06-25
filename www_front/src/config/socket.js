// src/config/socket.js
let socketUrl = 'ws://yii.loc:3000';

if (location.hostname === 'www.yii2.4n.com.ua') {
  socketUrl = 'wss://www.yii2.4n.com.ua:3000';
} else if (location.hostname === 'yii2.4n.com.ua') {
  socketUrl = 'wss://yii2.4n.com.ua:3000';
}

export default socketUrl;
