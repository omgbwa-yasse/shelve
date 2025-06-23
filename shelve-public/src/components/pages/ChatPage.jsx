import React from 'react';
import ChatInterface from '../chat/ChatInterface';

const ChatPage = () => {
  return (
    <div className="container mx-auto px-4 py-8">
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-4">
          Assistant virtuel
        </h1>
        <p className="text-lg text-gray-600">
          Posez vos questions et obtenez de l'aide pour naviguer dans les archives
        </p>
      </div>

      <ChatInterface />
    </div>
  );
};

export default ChatPage;
