import config from './config.js';

const queue = [];
let counter = 0;

const messageQueue = {
    queue,
    /**
     * @param {*} item
     */
    enqueue: item => {
        queue.push(item);
        counter++;
        while (queue.length > config.server.history) queue.shift();
    },
    /**
     * @return {*}
     */
    dequeue: () => queue.shift(),
    get counter() {
        return counter;
    },
};

export default messageQueue;