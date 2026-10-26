-- Ejecutar UNA sola vez sobre `cati`
USE `cati`;
-- Borrado lógico del chat: cada usuario oculta la conversación solo para él
ALTER TABLE `chat_conversacion`
  ADD COLUMN `OcultoU1` datetime NULL,
  ADD COLUMN `OcultoU2` datetime NULL;
